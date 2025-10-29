<?php

namespace App\Http\Controllers\LiveStreaming;

use Closure;
use App\Models\User;
use App\Models\Livestream;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\LivestreamLike;
use App\Models\LivestreamSave;
use Spatie\LaravelData\Optional;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Constants\LivestreamStatuses;
use App\Data\Dto\CreateLivestremData;
use App\Data\Dto\UpdateLivestremData;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LivestreamController extends Controller
{
    use AuthorizesRequests, DispatchesJobs;

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show', 'addedProducts']]);
    }

    public function index()
    {
        $livestreams = QueryBuilder::for(Livestream::class)
            ->with(['vendor:id,name,cover_image'])
            ->latest()
            ->cursorPaginate(1);

        $livestreams->getCollection()->transform(function ($livestream) {
            $coverImage = $livestream->vendor->cover_image ?? null;

            if ($coverImage && ! Str::startsWith($coverImage, ['http://', 'https://'])) {
                $coverImage = Storage::url($coverImage);
            }

            return [
                ...$livestream->toArray(),
                'status' => $livestream->status,

                'vendor' => [
                    'id' => $livestream->vendor->id ?? null,
                    'name' => $livestream->vendor->name ?? null,
                    'cover_image' => $coverImage,
                ],

                'recordings' => $livestream->recordings,
            ];
        });

        return response()->json($livestreams);
    }

    public function addedProducts($id)
    {
        $livestream = Livestream::with('vendor', 'products.images')->findOrFail($id);

        // Prepare vendor info
        $vendor = $livestream->vendor;
        $vendorData = null;
        if ($vendor) {
            $vendorData = [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'cover_image' => $vendor->cover_image
                    ? (Str::startsWith($vendor->cover_image, ['http://', 'https://'])
                        ? $vendor->cover_image
                        : Storage::url($vendor->cover_image))
                    : null,
            ];
        }

        // Prepare products
        $products = $livestream->products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->selling_price,
                'discount_price' => $product->discount_price,
                'first_image' => $product->firstImage
                    ? (Str::startsWith($product->firstImage->path, ['http://', 'https://'])
                        ? $product->firstImage->path
                        : Storage::url($product->firstImage->path))
                    : null,
            ];
        });

        return response()->json([
            'livestream_id' => $livestream->id,
            'title' => $livestream->title,
            'vendor' => $vendorData,
            'products' => $products,
        ]);
    }

    public function store(CreateLivestremData $createLivestremData, #[CurrentUser] User $user, GetLivestreamPublisherTokenController $controller)
    {
        $this->authorize('create-livestream', $user);

        try {
            DB::beginTransaction();
            $newLivestream = $user->livestreams()->create($createLivestremData->except('products')->toArray());
            $newLivestream->products()->attach($createLivestremData->products);
            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        $response = $controller->__invoke($newLivestream, $user);
        $publisherToken = data_get($response->getData(true), 'token');

        return $newLivestream->toResource()->additional([
            'published_token' => $publisherToken,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Livestream $ls)
    {
        $livestream = $ls->load(['livestreamProducts', 'comments', 'likes']);

        return $livestream->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLivestremData $updateLivestremData, $livestreamId)
    {
        /** @var Livestream */
        $livestream = Livestream::find($livestreamId);
        $livestream->fill($updateLivestremData->except('status')->toArray());

        Pipeline::send($updateLivestremData)
            ->through([
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if ($updateLivestremData->thumbnailPicture instanceof UploadedFile) {
                        $livestream->addMedia($updateLivestremData->thumbnailPicture)->toMediaCollection('thumbnail');
                    }

                    return $next($updateLivestremData);
                },
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if (LivestreamStatuses::STARTED === $updateLivestremData->status) {
                        $livestream->startRecording();
                    }

                    return $next($updateLivestremData);
                },
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if (LivestreamStatuses::FINISHED === $updateLivestremData->status) {
                        $livestream->stopRecording();
                    }

                    return $next($updateLivestremData);
                },
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if (! ($updateLivestremData->status instanceof Optional)) {
                        $livestream->setStatus($updateLivestremData->status->value);
                    }

                    return $next($updateLivestremData);
                },
            ])
            ->thenReturn();

        $livestream->save();

        return $livestream->toResource();
    }

    public function like($id)
    {
        // dd($request->all());
        $userId = auth()->id();

        $livestream = Livestream::findOrFail($id);

        $like = LivestreamLike::where('user_id', $userId)
            ->where('livestream_id', $livestream->id)
            ->first();

        if ($like) {
            $like->delete();

            return response()->json(['message' => 'Livestream unliked successfully'], 200);
        } else {
            LivestreamLike::create([
                'user_id' => $userId,
                'livestream_id' => $livestream->id,
            ]);

            return response()->json(['message' => 'Livestream liked successfully'], 200);
        }
    }

    public function save(Request $request, $id)
    {
        $userId = auth()->id();

        $livestream = Livestream::findOrFail($id);

        $save = LivestreamSave::where('user_id', $userId)
            ->where('livestream_id', $livestream->id)
            ->first();

        if ($save) {
            $save->delete();

            return response()->json(['message' => 'Livestream unsaved successfully'], 200);
        } else {
            LivestreamSave::create([
                'user_id' => $userId,
                'livestream_id' => $livestream->id,
            ]);

            return response()->json(['message' => 'Livestream saved successfully'], 200);
        }
    }

    public function getLikedLivestreams()
    {
        $userId = auth()->id();

        $likedLivestreamIds = LivestreamLike::where('user_id', $userId)
            ->pluck('livestream_id');

        $likedLivestreams = Livestream::select('id', 'title', 'vendor_id')
            ->with([
                'vendor:id,shop_name,cover_image',
            ])
            ->withCount(['likes', 'comments', 'participants as total_participants'])
            ->whereIn('id', $likedLivestreamIds)
            ->get()
            ->map(function ($livestream) {
                return [
                    'id' => $livestream->id,
                    'title' => $livestream->title,
                    'vendor' => $livestream->vendor,
                    'total_participants' => $livestream->total_participants ?? 0,
                    'likes_count' => $livestream->likes_count ?? 0,
                    'comments_count' => $livestream->comments_count ?? 0,
                    'recordings' => $livestream->recordings,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $likedLivestreams,
        ]);
    }

    public function getSavedLivestreams()
    {
        $userId = auth()->id();

        $savedLivestreamIds = LivestreamSave::where('user_id', $userId)
            ->pluck('livestream_id');

        $savedLivestreams = Livestream::select('id', 'title', 'vendor_id')
            ->with([
                'vendor:id,shop_name,cover_image',
            ])
            ->withCount([
                'likes',
                'comments',
                'participants as total_participants',
            ])
            ->whereIn('id', $savedLivestreamIds)
            ->get()
            ->map(function ($livestream) {
                return [
                    'id' => $livestream->id,
                    'title' => $livestream->title,
                    'vendor' => $livestream->vendor,
                    'total_participants' => $livestream->total_participants ?? 0,
                    'likes_count' => $livestream->likes_count ?? 0,
                    'comments_count' => $livestream->comments_count ?? 0,
                    'recordings' => $livestream->recordings,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $savedLivestreams,
        ]);
    }

    public function getLikesCount($id)
    {
        $livestream = Livestream::findOrFail($id);

        $likesCount = $livestream->likes()->count();

        return response()->json(['likes_count' => $likesCount]);
    }
}
