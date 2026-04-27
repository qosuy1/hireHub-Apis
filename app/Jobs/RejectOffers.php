<?php

namespace App\Jobs;

use App\Models\Project;
use App\Notifications\OfferRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RejectOffers implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = 10;  // the time between every try in s
    /**
     * Create a new job instance.
     */
    public function __construct(private Project $project)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $acceptedOfferId = $this->project->acceptedOffer->id;

        $otherOffers = $this->project->offers()
            ->where('id', '!=', $acceptedOfferId)
            ->with('freelancer')
            ->get();

        // update statuses to rejected
        $this->project->offers()
            ->where('id', '!=', $acceptedOfferId)
            ->update(['status' => 'rejected']);

        // notify each rejected freelancer with their own offer
        $otherOffers->each(
            fn($rejectedOffer) => $rejectedOffer->freelancer->notify(new OfferRejectedNotification($rejectedOffer))
        );
    }
}
