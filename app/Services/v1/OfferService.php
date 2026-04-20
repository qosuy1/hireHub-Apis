<?php

namespace App\Services\v1;

use App\Enums\UserTypeEnum;
use App\Helper\V1\ApiResponse;
use App\Helper\V1\SaveAttachmentTrait;
use App\Http\Resources\v1\OfferResource;
use App\Models\Offer;
use App\Models\Project;
use App\Models\User;

class OfferService
{
    public function __construct(private AttachmentService $attachmentService)
    {
    }
    public function submitOffer(User $freelancer, Project $project, array $data)
    {
        // insure that the project owner he is not the freelancer
        if ($project->client_id === $freelancer->id) {
            throw new \Exception("you can't apply for your own project");
        }
        // the project status should be open 
        if ($project->status !== 'open') {
            throw new \Exception("this project is closed and not accepting offers");
        }

        // don't applay for the same project towic
        $exists = $project->offers()->where('freelancer_id', $freelancer->id)->exists();
        if ($exists) {
            throw new \Exception("you already applied for this project");
        }


        $data["freelancer_id"] = $freelancer->id;
        $offer = $project->offers()->create($data);

        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $this->attachmentService->upload($offer, $data['attachments'], 'offers');
        }
        return $offer->load('attachments');
    }

    public function updateOffer(Offer $offer, array $data)
    {
        $offer->update($data);
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $this->attachmentService->upload($offer, $data['attachments'], 'offers');
        }
        return $offer->load('attachments');
    }

    public function showOffer(Offer $offer)
    {
        $relations = ['freelancer' , 'project'];

        if ($offer->status === 'accepted') {
            $relations[] = 'attachments';
            // $relations[] = 'review';
        }

        return $offer->load($relations);
    }
    public function deleteOffer(Offer $offer): bool
    {
        if (
            auth()->user->type === UserTypeEnum::CLIENT->value
            ||
            (auth()->user->type === UserTypeEnum::FREELANCER->value
                &&
                $offer->user_id !== auth()->user()->id)
        )
            return false;

        // delete the attachments
        foreach ($offer->attachments as $attachment) {
            $this->attachmentService->delete($offer, $attachment->id);
        }
        return $offer->delete();
    }


}