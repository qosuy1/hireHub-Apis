<?php

namespace App\Infrastructure\Notification;


use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Message;


class Mailer
{
    public function send(string $to, string $subject, string $body): void
    {
        // this is the old way to send email
        // mail($to, $subject, $body);
        // this is the new way to send email
        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)
                ->subject($subject);
        });
    }
}