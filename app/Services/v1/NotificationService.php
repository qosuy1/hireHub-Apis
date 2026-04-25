<?php

namespace App\Services\v1;

use App\Interfaces\NotifierInterface;
use App\Models\Offer;
use App\Models\Project;
use App\Models\User;

class NotificationService
{
    public function __construct(private NotifierInterface $notifier)
    {
    }

    public function notifyOfferAccepted(Offer $offer): void
    {
        $this->notifier->send(
            $offer->freelancer,
            "Your offer on '{$offer->project->title}' was accepted!"
        );
    }

    public function notifyOfferRejected(Offer $offer): void
    {
        $this->notifier->send(
            $offer->freelancer,
            "Your offer on '{$offer->project->title}' was rejected!"
        );
    }

    public function notifyOfferCreated(Project $project): void
    {
        $this->notifier->send(
            $project->user,
            "You have a new offer on project '{$project->title}'"
        );
    }
    public function send(User $user, string $message): void
    {
        $this->notifier->send(
            $user,
            $message
        );
    }
}