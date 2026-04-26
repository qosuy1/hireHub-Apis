<?php

namespace App\Services\v1;

use App\Enums\UserTypeEnum;
use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Review;
use App\Models\User;
use App\Notifications\NewReviewNotification;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Leave a review on the freelancer who won the project.
     */
    public function reviewFreelancer(Project $project, User $user, array $data): bool
    {
        // Only the project owner (client) may leave reviews.
        if ($user->type !== UserTypeEnum::CLIENT->value || $project->user_id !== $user->id) {
            return false;
        }

        $offer = $project->acceptedOffer()->with('freelancerProfile.user')->first();

        if (!$offer || !$offer->freelancerProfile) {
            return false;
        }

        $profile = $offer->freelancerProfile;

        // One review per client per project
        if ($profile->reviews()->where('project_id', $project->id)->exists()) {
            return false;
        }

        return DB::transaction(function () use ($project, $user, $data, $profile) {
            $review = $profile->reviews()->create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
            ]);

            $this->updateFreelancerRating($profile);

            // notify freelancer
            if ($profile->user) {
                $profile->user->notify(new NewReviewNotification($review));
            }

            return true;
        });
    }

    /**
     * Leave a review on the project itself.
     */
    public function reviewProject(Project $project, User $user, array $data): bool
    {
        // Only the project owner (client) may leave reviews.
        if ($user->type !== UserTypeEnum::CLIENT->value || $project->user_id !== $user->id) {
            return false;
        }

        // One review per client per project
        if ($project->review()->where('user_id', $user->id)->where('project_id', $project->id)->exists()) {
            return false;
        }

        $project->review()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'reviewable_id' => $project->id,
            'reviewable_type' => Project::class,
        ]);

        return true;
    }

    /**
     * Update an existing review.
     */
    public function updateReview(Review $review, User $user, array $data): bool
    {
        if ($review->user_id !== $user->id) {
            return false;
        }

        return DB::transaction(function () use ($review, $data) {
            $oldRating = $review->rating;

            $review->update([
                'rating' => $data['rating'] ?? $review->rating,
                'comment' => $data['comment'] ?? $review->comment,
            ]);

            // recalculate freelancer rating only if it changed
            if ($review->reviewable_type === FreelancerProfile::class && $oldRating != $review->fresh()->rating) {
                $this->updateFreelancerRating(FreelancerProfile::find($review->reviewable_id));
            }

            return true;
        });
    }

    /**
     * Delete an existing review.
     */
    public function deleteReview(Review $review, User $user): bool
    {
        if ($review->user_id !== $user->id) {
            return false;
        }

        return DB::transaction(function () use ($review) {
            $reviewableType = $review->reviewable_type;
            $reviewableId = $review->reviewable_id;

            $review->delete();

            if ($reviewableType === FreelancerProfile::class) {
                $this->updateFreelancerRating(FreelancerProfile::find($reviewableId));
            }

            return true;
        });
    }

    /**
     * Recalculate and persist a freelancer profile's average rating.
     */
    private function updateFreelancerRating(FreelancerProfile $profile): void
    {
        $profile->update([
            'average_rating' => $profile->reviews()->avg('rating') ?? 0,
        ]);
    }
}
