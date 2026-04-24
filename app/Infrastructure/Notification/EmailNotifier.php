<?php

namespace App\Infrastructure\Notification;

use App\Infrastructure\Notification\Mailer;
use App\Interfaces\NotifierInterface;
use App\Models\User;

class EmailNotifier implements NotifierInterface
{
    public function __construct(private Mailer $mailer) {}

    public function send(User $user, string $message)
    {
        $this->mailer->send(
            to: $user->email,
            subject: "Notification",
            body: $message
        );
    }
}
