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
use App\Constants\LivestreamStatuses;
use App\Data\Dto\CreateLivestremData;
use App\Data\Dto\UpdateLivestremData;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LivestreamController extends Controller
{
    use AuthorizesRequests, DispatchesJobs;

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $livestreams = QueryBuilder::for(Livestream::class)
            ->latest()
            ->paginate();

        return $livestreams->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLivestremData $createLivestremData, #[CurrentUser] User $user, GetLivestreamPublisherTokenController $controller)
    {
        $this->authorize('create-livestream', $user);

        try {
            DB::beginTransaction();
            $newLivestream = $user->livestreams()->create($createLivestremData->toArray());
            $newLivestream->products()->attach($createLivestremData->products);
            $response = $controller->__invoke($newLivestream, $user);
            $publisherToken = data_get($response->getData(true), 'token');
            DB::commit();

            return $newLivestream->toResource()->additional([
                'published_token' => $publisherToken,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
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
        $livestream->fill($updateLivestremData->toArray());

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

        return response()->json($likedLivestreamIds);
    }

    public function getSavedLivestreams()
    {
        $userId = auth()->id();

        $savedLivestreams = LivestreamSave::where('user_id', $userId)
            ->pluck('livestream_id');

        return response()->json($savedLivestreams);
    }

    public function getLikesCount($id)
    {
        $livestream = Livestream::findOrFail($id);

        $likesCount = $livestream->likes()->count();

        return response()->json(['likes_count' => $likesCount]);
    }
}
