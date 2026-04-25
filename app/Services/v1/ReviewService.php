<?php

namespace App\Services\v1;

use App\Enums\UserTypeEnum;
use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewService
{

    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * Leave a review on the freelancer who won the project.
     * Eager-loads freelancerProfile once to avoid N+1.
     */
    public function reviewFreelancer(Project $project, User $user, array $data): bool
    {
        // Only the project owner (client) may leave reviews.
        if ($user->type !== UserTypeEnum::CLIENT->value || $project->user_id !== $user->id) {
            return false;
        }

        $offer = $project->acceptedOffer()->with('freelancerProfile.user')->first();

        if (!$offer || !$offer->freelancerProfile) {
            return false; // No accepted offer or freelancer profile found.
        }

        $profile = $offer->freelancerProfile;
        // This client must not have already reviewed this freelancer on this project.
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

            $this->updateFreelancerRating($review);

            $freelancer = $profile->user;
            if ($freelancer) {
                $this->notificationService->send($freelancer, "You have a new review on project {$project->title}");
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
        // This client must not have already reviewed this project.
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

            // update freelancer rating
            if ($review->reviewable_type === FreelancerProfile::class && $oldRating != $review->rating) {
                $this->updateFreelancerRating($review);
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

            // delete review
            $review->delete();

            // update freelancer rating
            if ($reviewableType === FreelancerProfile::class) {
                $this->updateFreelancerRating($review);
            }

            return true;
        });
    }

    // helper function to update Freelancer Rating
    private function updateFreelancerRating(Review $review)
    {
        $profile = FreelancerProfile::find($review->reviewable_id);
        if ($profile != null) {
            $profile->update([
                'average_rating' => $profile->reviews()->avg('rating') ?? 0,
            ]);
        }
    }
}
