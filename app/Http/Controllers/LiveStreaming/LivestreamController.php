<?php

namespace App\Http\Controllers\LiveStreaming;

use App\Constants\LivestreamStatuses;
use App\Data\Dto\CreateLivestremData;
use App\Data\Dto\UpdateLivestremData;
use App\Data\Resources\LivestreamData;
use App\Http\Resources\LivestreamResource;
use App\Models\Livestream;
use App\Models\LivestreamLike;
use App\Models\LivestreamSave;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as Controller;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Pipeline;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

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
            ->orderBy('created_at', 'desc')
            ->paginate();

        return $livestreams;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLivestremData $createLivestremData)
    {
        $vendor = User::find($createLivestremData->vendorId);
        // $this->authorize('create-livestream', $vendor);

        /** @var Livestream */
        $newLivestream = Livestream::create($createLivestremData->toArray());

        // $newLivestream->addAllMediaFromTokens($createLivestremData->thumbnailPicture, 'thumbnail');

        // return LivestreamData::from($newLivestream);
        return $newLivestream->toResource();
    }

    /**
     * Display the specified resource.
     */
    public function show($livestreamId)
    {
        $livestream = Livestream::with(['livestreamProducts', 'comments', 'likes'])->findOrFail($livestreamId);
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
                    if ($updateLivestremData->status === LivestreamStatuses::STARTED) {
                        $livestream->startRecording();
                    }

                    return $next($updateLivestremData);
                },
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if ($updateLivestremData->status === LivestreamStatuses::FINISHED) {
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
