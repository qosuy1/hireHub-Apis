<?php

namespace App\Http\Resources\v1;

use App\Http\Resources\v1\ProjectResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
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
            'project_id' => $this->project_id,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'freelancer_id' => $this->freelancer_id,
            'freelancer' => new UserResource($this->whenLoaded('freelancer')),
            'cover_letter' => $this->cover_letter,
            'status' => $this->status,
            'delivery_days' => $this->delevery_time,
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),

            $this->mergeWhen($this->status === 'pending', [
                'amount' => $this->amount,
            ]),
            $this->mergeWhen($this->status === 'accepted', [
                'accepted_at' => Carbon::parse($this->updated_at)->format('Y-m-d h:i A'),
            ]),
            $this->mergeWhen($this->status === 'rejected', [
                'rejected_at' => Carbon::parse($this->updated_at)->format('Y-m-d h:i A'),
            ]),
        ];
    }
}
