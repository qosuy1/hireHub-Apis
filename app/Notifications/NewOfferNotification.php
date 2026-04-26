<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOfferNotification extends Notification
{
    use Queueable;

    public function __construct(private Project $project)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You Have a New Offer!')
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("You have received a new offer on your project **'{$this->project->title}'**.")
            ->action('View Offers', url("/projects/{$this->project->id}/offers"))
            ->line('Thank you for using HireHub!');
    }
}
