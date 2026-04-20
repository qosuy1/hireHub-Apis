<?php

namespace App\Observers;

use App\Models\FreelancerProfile;
use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        if ($review->reviewable_type == FreelancerProfile::class) {
            $profile = FreelancerProfile::find($review->reviewable_id);
            if ($profile != null) {
                $profile->update([
                    'average_rating' => $profile->reviews()->avg('rating') ?? 0,
                ]);
            }
        }
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        if ($review->reviewable_type == FreelancerProfile::class) {
            $profile = FreelancerProfile::find($review->reviewable_id);
            if ($profile != null) {
                $profile->update([
                    'average_rating' => $profile->reviews()->avg('rating') ?? 0,
                ]);
            }
        }
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        if ($review->reviewable_type == FreelancerProfile::class) {
            $profile = FreelancerProfile::find($review->reviewable_id);
            if ($profile != null) {
                $profile->update([
                    'average_rating' => $profile->reviews()->avg('rating') ?? 0,
                ]);
            }
        }
    }


}
