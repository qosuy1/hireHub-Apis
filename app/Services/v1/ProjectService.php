<?php

namespace App\Services\v1;

use App\Enums\UserTypeEnum;
use App\Helper\V1\ApiResponse;
use App\Helper\V1\SaveAttachmentTrait;
use App\Models\Offer;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectService
{

    public function __construct(private AttachmentService $attachmentService, private NotificationService $notifier)
    {
    }
    public function getAllProjects($filters = [])
    {
        // the problem here is when we get the projects,
        //  we get all of them in the same page and in the same time
        // so if i have 1000 project i will get all of them

        // to solve this proble we need to use => paginate()
        return Project::open()->withCount('offers')->with('tags')->budgetAbove($filters['min_budget'] ?? null)
            ->when($filters['this_month'] ?? null, fn($q) => $q->thisMonth())
            ->latest()->paginate(15);
    }

    public function store($data)
    {
        return DB::transaction(function () use ($data) {

            $tags = is_string($data['tags'] ?? null) ? json_decode($data['tags'], true) : ($data['tags'] ?? []);

            $project = Project::create($data);
            if (!empty($tags)) {
                $project->tags()->sync($tags);
            }

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                $this->attachmentService->upload($project, $data['attachments'], 'projects');
            }

            return $project->load('tags', 'attachments');
        });
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        if (isset($data['tags'])) {
            $tags = is_string($data['tags']) ? json_decode($data['tags'], true) : $data['tags'];
            $project->tags()->sync($tags);
        }

        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $this->attachmentService->upload($project, $data['attachments'], 'projects');
        }

        return $project->load('tags', 'attachments');
    }

    public function delete(Project $project)
    {
        // check the user type only project owner or admin can delete the project 
        if (
            auth()->user->type === UserTypeEnum::FREELANCER->value
            ||
            (auth()->user->type === UserTypeEnum::CLIENT->value
                &&
                $project->user_id !== auth()->user()->id)
        )
            return ApiResponse::forbidden();

        // Delete attachment files from storage and database
        foreach ($project->attachments as $attachment) {
            $this->attachmentService->delete($project, $attachment->id);
        }

        $project->tags()->detach();
        return $project->delete();
    }


    public function acceptOffer(Project $project, Offer $offer)
    {
        $user = request()->user();
        if ($user->type === UserTypeEnum::CLIENT->value && $project->user_id === $user->id) {
            $offer->update([
                'status' => 'accepted',
                'updated_at' => now()
            ]);
            $this->notifier->notifyOfferAccepted($offer);
            
            $project->update([
                'status' => "in_progress"
            ]);
            $project->offers()->where('id', '!=', $offer->id)->update([
                'status' => 'rejected'
            ]);

            $rejectedOffers = $project->offers()->with('freelancer')->where('status', 'rejected')->get();
            foreach ($rejectedOffers as $rejectedOffer) {
                $this->notifier->notifyOfferRejected($rejectedOffer);
            }
            return true;
        }
        return false;
    }

}
