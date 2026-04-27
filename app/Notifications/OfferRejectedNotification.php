<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class OfferRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    // try 3 times if the job failed
    public $tries = 3;


    public function __construct(private Offer $offer)
    {
        $this->delay(2);
        $this->queue = 'notifications';
    }
    public function backoff() {
        return ;
    }
    public function faild(Throwable $exception){
        Log::error("sending mail is failed after 3 tries : " . $exception->getMessage());
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on Your Offer')
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("Unfortunately, your offer on the project **'{$this->offer->project->title}'** was not selected this time.")
            ->line('Don\'t be discouraged — there are plenty of other great projects waiting for you.')
            ->action('Browse Projects', url('/projects'))
            ->line('Thank you for using HireHub!');
    }
}
