<?php

namespace App\Services\v1;

use App\Enums\UserTypeEnum;
use App\Jobs\RejectOffers;
use App\Jobs\SendProjectCreatedEmail;
use App\Models\Offer;
use App\Models\Project;
use App\Notifications\OfferAcceptedNotification;
use App\Notifications\ProjectCreatedNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProjectService
{

    public function __construct(private AttachmentService $attachmentService)
    {
    }

    public function getAllProjects($filters = [])
    {
        // default: open projects only; pass ?all=1 to get all statuses
        return Project::query()
            ->when(empty($filters['all']), fn($q) => $q->open()) // default: open only
            ->withCount('offers')
            ->with('tags')
            ->budgetAbove($filters['min_budget'] ?? null)
            ->when($filters['this_month'] ?? null, fn($q) => $q->thisMonth())
            ->latest()
            ->paginate(15)
            ->appends($filters);
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

            DB::afterCommit(function () use ($project) {
                // delete the old cache
                Cache::tags(['projects'])->flush();
                // send notification for the client
                $project->user->notify(new ProjectCreatedNotification($project));
            });
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
        Cache::tags(['projects'])->flush();

        return $project->load('tags', 'attachments');
    }

    /**
     * Returns false if unauthorized, true on success.
     * The controller is responsible for returning the HTTP response.
     */
    public function delete(Project $project): bool
    {
        $user = request()->user();

        // Only the project owner (client) can delete their own project
        if ($user->type === UserTypeEnum::FREELANCER->value) {
            return false;
        }
        if ($user->type === UserTypeEnum::CLIENT->value && $project->user_id !== $user->id) {
            return false;
        }

        return DB::transaction(function () use ($project) {

            // Delete attachment files from storage and database
            foreach ($project->attachments as $attachment) {
                $this->attachmentService->delete($project, $attachment->id);
            }

            $project->tags()->detach();
            $deleted = $project->delete();

            DB::afterCommit(function () {
                // delete the old cache
                Cache::tags(['projects'])->flush();
            });

            return $deleted;

        });
    }

    public function acceptOffer(Project $project, Offer $offer): bool
    {
        $user = request()->user();
        if ($user->type !== UserTypeEnum::CLIENT->value || $project->user_id !== $user->id) {
            return false;
        }

        return DB::transaction(function () use ($project, $offer) {
            // update accepted offer status
            $this->updateObjectStatus($offer, 'accepted');

            // update project status
            $this->updateObjectStatus($project, 'in_progress');

            DB::afterCommit(function () use ($project, $offer) {
                // reject other offers + notify their owners (via job)
                dispatch(new RejectOffers($project));

                // notify the accepted freelancer
                $offer->freelancer->notify(new OfferAcceptedNotification($offer));
            });

            return true;
        });
    }

    private function updateObjectStatus(Offer|Project|HasMany $object, string $status): void
    {
        $object->update([
            'status' => $status
        ]);
    }
}
