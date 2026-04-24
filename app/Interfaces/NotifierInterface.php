<?php

namespace App\Interfaces;

use App\Models\User;

interface NotifierInterface
{
    public function send(User $user, string $message);
}
