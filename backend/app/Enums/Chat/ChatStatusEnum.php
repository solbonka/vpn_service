<?php

namespace App\Enums\Chat;

enum ChatStatusEnum: string
{
    case ACTIVE = 'active';
    case PASSIVE = 'passive';
    case BLOCKED_TRIAL = 'blocked_trial';
    case BLOCKED_PAID = 'blocked_paid';
    case NO_SUBSCRIPTION = 'no_subscription';
}

