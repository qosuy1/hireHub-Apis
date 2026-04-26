<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FreelancerProfileResource extends JsonResource
{
    public static $wrap = 'data';
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // 'user' => new UserResource($this->whenLoaded('user')),
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'bio' => $this->bio,
            'hourly_rate' => $this->hourly_rate,
            'avatar' => $this->avatar_url,
            'portfolio_links' => $this->portfolio_links,
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            'availability' => $this->availability_status,
            'rating' => $this->display_rating,
            'reviews_count' => $this->reviews_count ?? 0,
            'reviews' => $this->whenLoaded('reviews', function () {
                return ReviewResource::collection($this->reviews);
            }),
            'offers_count' => $this->offers_count ?? 0,
            'offers' => $this->whenLoaded('offers', function () {
                return OfferResource::collection($this->offers);
            }),
            'accepted_projects_count' => $this->acceptedProjects_count ?? 0,
            'accepted_projects' => $this->whenLoaded('acceptedProjects', function () {
                return ProjectResource::collection($this->acceptedProjects);
            }),
        ];
    }
}
