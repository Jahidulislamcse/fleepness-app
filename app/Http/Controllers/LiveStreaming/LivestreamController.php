<?php

namespace App\Http\Controllers\LiveStreaming;

use App\Constants\GateNames;
use App\Constants\LivestreamStatuses;
use App\Data\Dto\CreateLivestremData;
use App\Data\Dto\UpdateLivestremData;
use App\Data\Resources\LivestreamData;
use App\Models\Livestream;
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
    public function index(): PaginatedDataCollection
    {
        $livestreams = QueryBuilder::for(Livestream::class)
            ->paginate();

        return new PaginatedDataCollection(LivestreamData::class, $livestreams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLivestremData $createLivestremData): Livestream
    {
        $vendor = User::find($createLivestremData->vendorId);
        // $this->authorize('create-livestream', $vendor);

        /** @var Livestream */
        $newLivestream = Livestream::create($createLivestremData->toArray());

        // $newLivestream->addAllMediaFromTokens($createLivestremData->thumbnailPicture, 'thumbnail');

        // return LivestreamData::from($newLivestream);
        return $newLivestream;
    }

    /**
     * Display the specified resource.
     */
    public function show(Livestream $livestream): LivestreamData
    {
        return LivestreamData::from($livestream);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLivestremData $updateLivestremData,  $livestreamId)
    {
        /** @var Livestream */
        $livestream = Livestream::find($livestreamId);
        // $this->authorize(GateNames::UPDATE_LIVESTREAM->value, $livestream);

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
                        $livestream->started_at = now();
                    }

                    return $next($updateLivestremData);
                },
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if ($updateLivestremData->status === LivestreamStatuses::FINISHED) {
                        $livestream->ended_at = now();
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

        return LivestreamData::from($livestream);
    }
}
