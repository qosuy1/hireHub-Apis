<?php

namespace App\Http\Resources\v1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'budget' => $this->formatted_budget,
            'type' => $this->type,
            'status' => $this->status,

            'delivery_date' => $this->when(
                $request->routeIs('projects.show') || $request->routeIs('projects.accept-offer'),
                Carbon::parse($this->delivery_date)->format('Y-m-d'),
                Carbon::parse($this->delivery_date)->format('Y-m-d'),
            ),
            'left_days' => $this->left_days,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d h:i A'),
            'description' => $this->when(
                $request->routeIs('projects.show'),
                $this->description,
                str($this->description)->limit(100)
            ),
            'accepted_offer' => $this->when(
                $request->routeIs('projects.show') || $request->routeIs('projects.accept-offer') || $request->routeIs('freelancer-profiles.show') ,
                new OfferResource($this->whenLoaded('acceptedOffer')),
            ),
            // Only show proposals count in the list, but show the full list in 'show'
            'offers_count' => $this->whenCounted('offers'),
            'offers' => OfferResource::collection($this->whenLoaded('offers')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),


            'owner' => new UserResource($this->whenLoaded('user')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'review' => $this->when(
                $this->review != null,
                new ReviewResource($this->review),
                "no review yet"
            ),
            // 'updated_at' => Carbon::parse($this->created_at)->format('Y-m-d h:i A'),

        ];
    }
}
