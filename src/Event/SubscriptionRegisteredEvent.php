<?php

namespace App\Event;

use App\Entity\User;

class SubscriptionRegisteredEvent
{
    public const NAME = 'subscription.registered';

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}