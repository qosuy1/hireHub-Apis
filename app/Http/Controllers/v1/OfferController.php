<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\offer\StoreOfferRequest;
use App\Http\Requests\V1\offer\UpdateOfferRequest;
use App\Http\Resources\v1\OfferResource;
use App\Models\Offer;
use App\Models\Project; 
use App\Services\v1\OfferService;

class OfferController extends Controller
{

    public function __construct(private OfferService $offerService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {

    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOfferRequest $request, Project $project)
    {
        $this->offerService->submitOffer($request->user(), $project, $request->validated());
        return ApiResponse::success('the offer submited successfully !');
    }

    /**
     * Display the specified resource.
     */
    public function show(Offer $offer)
    {
        if (!$offer)
            return ApiResponse::notFound('the offer not found !');

        return ApiResponse::success(
            new OfferResource($this->offerService->showOffer($offer))
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfferRequest $request, Offer $offer)
    {
        if (!$offer)
            return ApiResponse::notFound('the offer not found !');

        $this->offerService->updateOffer($offer, $request->validated());
        return ApiResponse::success('the offer updated successfully !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Offer $offer)
    {
        if (!$offer)
            return ApiResponse::notFound('the offer not found !');

        if ($this->offerService->deleteOffer($offer))
            return ApiResponse::success(null, 'offer deleted successfully !');
        return ApiResponse::forbidden('this user cann\'t delete the offer ');
    }
 
}
