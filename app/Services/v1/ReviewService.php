<?php

namespace App\Services\v1;

use App\Enums\UserTypeEnum;
use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\User;

class ReviewService
{
    /**
     * Create a review for a freelancer profile or a project.
     */
    public function createReview(Project $project, User $user, array $data): bool
    {
        // Only the project owner (client) may leave reviews.
        if ($user->type !== UserTypeEnum::CLIENT->value || $project->user_id !== $user->id) {
            return false;
        }

        if ($data['reviewable_type'] === 'freelancer_profiles') {
            return $this->reviewFreelancer($project, $user, $data);
        }

        return $this->reviewProject($project, $user, $data);
    }

    /**
     * Leave a review on the freelancer who won the project.
     * Eager-loads freelancerProfile once to avoid N+1.
     */
    private function reviewFreelancer(Project $project, User $user, array $data): bool
    {
        // Eager-load freelancerProfile in a single query to avoid N+1
        $offer = $project->acceptedOffer()->with('freelancerProfile')->first();

        if (!$offer || !$offer->freelancerProfile) {
            return false; // No accepted offer or freelancer profile found.
        }

        $profile = $offer->freelancerProfile;

        // The requested profile must match the accepted offer's freelancer.
        if ($profile->id != $data['reviewable_id']) {
            return false;
        }
        $reviews = $profile->reviews();
        // This client must not have already reviewed this freelancer on this project.
        if ($reviews->where('user_id', $user->id)->exists()) {
            return false;
        }       

        $reviews->create([
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'reviewable_id' => $profile->id,
            'reviewable_type' => FreelancerProfile::class,
        ]);
        $profile->update([
            'average_rating' => $reviews->avg('rating'),
        ]);

        return true;
    }

    /**
     * Leave a review on the project itself.
     */
    private function reviewProject(Project $project, User $user, array $data): bool
    {
        // This client must not have already reviewed this project.
        if ($project->review()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $project->review()->create([
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'reviewable_id' => $project->id,
            'reviewable_type' => Project::class,
        ]);

        return true;
    }
}
