<?php

namespace App\Http\Controllers\LiveStreaming;

use App\Models\Livestream;
use Illuminate\Routing\Controller;
use App\Data\Dto\AddLivestreamProductData;
use App\Data\Dto\RemoveLivestreamProductData;

class LivestreamProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($livestreamId, AddLivestreamProductData $data)
    {
        $livestream = Livestream::findOrFail($livestreamId);
        $livestream->products()->sync($data->productIds, false);

        return $livestream->toResource();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($livestreamId, RemoveLivestreamProductData $data)
    {
        $livestream = Livestream::findOrFail($livestreamId);
        $livestream->products()->detach($data->productIds);

        return $livestream->toResource();
    }
}
