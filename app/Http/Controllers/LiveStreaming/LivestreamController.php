<?php

namespace App\Http\Controllers\LiveStreaming;

use Closure;
use App\Models\User;
use App\Models\Livestream;
use Illuminate\Http\Request;
use App\Models\LivestreamLike;
use App\Models\LivestreamSave;
use Spatie\LaravelData\Optional;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Constants\LivestreamStatuses;
use App\Data\Dto\CreateLivestremData;
use App\Data\Dto\UpdateLivestremData;
use Illuminate\Support\Facades\Pipeline;
use App\Http\Resources\LivestreamResource;
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
        $livestreams = Livestream::with(['vendor'])
            ->latest()
            ->cursorPaginate();

        return LivestreamResource::collection($livestreams);
    }

    public function addedProducts(Livestream $livestream)
    {
        $livestream->load([
            'vendor',
            'products' => [
                'images', 'tag.grandParent',
            ],
        ]);

        return LivestreamResource::make($livestream);

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
                        $livestream->status = $updateLivestremData->status;
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
        $userId = Auth::id();

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
        $userId = Auth::id();

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
        $userId = Auth::id();

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
        $userId = Auth::id();

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
