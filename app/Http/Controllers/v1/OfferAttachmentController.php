<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Http\Requests\v1\Attachment\StoreAttachmentRequest;
use App\Http\Resources\v1\AttachmentResource;
use App\Models\Attachment;
use App\Models\Offer;
use App\Services\v1\AttachmentService;

class OfferAttachmentController extends Controller
{
    public function __construct(private AttachmentService $attachment_service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Offer $offer)
    {
        if (!$offer)
            return ApiResponse::notFound();
        return ApiResponse::success(AttachmentResource::collection($offer->attachments));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttachmentRequest $request, Offer $offer)
    {
        if (!$offer)
            return ApiResponse::notFound('offer not found');
        $this->attachment_service->upload($offer, $request->validated(), 'offers');
        return ApiResponse::success([], 'attachment uploaded successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Offer $offer, Attachment $attachment)
    {
        if (!$offer)
            return ApiResponse::notFound('offer not found');
        if (!$attachment)
            return ApiResponse::notFound("attachment with id : $attachment->id for offer id: $offer->id  not found");

        $this->attachment_service->delete($offer, $attachment->id);
        return ApiResponse::success([], 'attachment deleted successfully');
    }
}
