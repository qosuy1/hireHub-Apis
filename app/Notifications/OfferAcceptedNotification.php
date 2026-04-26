<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(private Offer $offer)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Offer Was Accepted!')
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("Great news! Your offer on the project **'{$this->offer->project->title}'** has been accepted.")
            ->line('The client is ready to work with you. Please get in touch to start.')
            ->action('View Project', url("/projects/{$this->offer->project->id}"))
            ->line('Thank you for using HireHub!');
    }
}
