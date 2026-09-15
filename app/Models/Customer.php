<?php

namespace App\Models;

use App\Traits\HasActivities;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes, HasNotes, HasActivities, HasAttachments, HasTags;

    protected $fillable = [
        'name', 'email', 'phone', 'company_id', 'industry',
        'lifetime_value', 'owner_id', 'last_activity_at', 'custom_fields',
    ];

    protected function casts(): array
    {
        return [
            'lifetime_value' => 'decimal:2',
            'last_activity_at' => 'datetime',
            'custom_fields' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function openInvoicesCount(): int
    {
        return $this->invoices()->whereIn('status', ['Pending', 'Overdue'])->count();
    }
}
