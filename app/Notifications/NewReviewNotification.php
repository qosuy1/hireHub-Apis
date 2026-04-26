<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification
{
    use Queueable;

    public function __construct(private Review $review)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You Have a New Review!')
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("A client has left you a review for the project **'{$this->review->project->title}'**.")
            ->line("**the rating is: {$this->review->rating}**")
            ->line("**the comment is: {$this->review->comment}**")
            ->action('View Your Profile', route('freelancer-profiles.show', $this->review->freelancer_profile->id))
            ->line('Thank you for using' . config('app.name') . ' !');
    }
}
