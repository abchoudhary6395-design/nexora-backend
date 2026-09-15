<?php

namespace App\Policies;

use App\Models\CalendarEvent;
use App\Models\User;

class CalendarEventPolicy
{
    public function update(User $user, CalendarEvent $event): bool
    {
        return $event->created_by === $user->id || $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function delete(User $user, CalendarEvent $event): bool
    {
        return $this->update($user, $event);
    }
}
