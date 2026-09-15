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
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasNotes, HasActivities, HasAttachments, HasTags;

    protected $fillable = [
        'name', 'industry', 'website', 'logo', 'employees',
        'annual_revenue', 'country', 'city', 'address', 'owner_id',
    ];

    protected function casts(): array
    {
        return ['annual_revenue' => 'decimal:2'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function openDealsCount(): int
    {
        return $this->deals()->whereHas(
            'stage',
            fn ($q) => $q->where('is_won', false)->where('is_lost', false)
        )->count();
    }
}
