<?php

namespace App\Models;

use App\Traits\HasActivities;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes, HasNotes, HasActivities, HasAttachments;

    protected $fillable = [
        'name', 'customer_id', 'description', 'status', 'progress',
        'budget', 'start_date', 'end_date', 'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot('role_on_project');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    /** Recalculates progress from completed vs total tasks. Call after task status changes. */
    public function recalculateProgress(): void
    {
        $total = $this->tasks()->count();
        if ($total === 0) return;

        $done = $this->tasks()->where('status', 'done')->count();
        $this->update(['progress' => (int) round(($done / $total) * 100)]);
    }
}
