<?php

namespace App\Jobs;

use App\Models\FreelancerProfile;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateFreelancerProfileRating implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $freelancerProfile_id;
    /**
     * Create a new job instance.
     */
    public function __construct($freelancerProfile_id)
    {
        $this->freelancerProfile_id = $freelancerProfile_id;
        $this->afterCommit();
    }

    /**
     * make unique id for the job depends on profile_id
     * in case multiple job are dispatched with same profile_id
     * it will be excuted only once
     */
    public function uniqueId(): string
    {
        return (string) $this->freelancerProfile_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // lockForUpdate => to avoid update the profile multiable times in the same time
        DB::transaction(function () {
            $profile = FreelancerProfile::where('id', $this->freelancerProfile_id)
                ->lockForUpdate()->first();
            if ($profile) {
                $averageRating = $profile->reviews()->avg('rating');
                $profile->update([
                    'average_rating' => round($averageRating, 1),
                ]);
            }
        });
    }
}
