<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActivities
{
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }

    /**
     * Convenience helper to log an activity against this model.
     * Usage: $lead->logActivity('lead_updated', 'Status changed to Qualified');
     */
    public function logActivity(string $type, string $description, array $meta = []): Activity
    {
        return $this->activities()->create([
            'user_id' => auth()->id(),
            'type' => $type,
            'description' => $description,
            'meta' => $meta,
        ]);
    }
}
