<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GroupTask extends Model
{
    protected $fillable = [
        'group_id',
        'assigned_to',
        'created_by',
        'name',
        'description',
        'priority',
        'deadline',
        'progress',
        'progress_note',
        'progress_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'progress' => 'integer',
            'progress_updated_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Every progress report ever sent for this task
    public function updates(): HasMany
    {
        return $this->hasMany(GroupTaskUpdate::class);
    }

    // Only the newest report (waiting, approved or rejected)
    public function latestUpdate(): HasOne
    {
        return $this->hasOne(GroupTaskUpdate::class)->latestOfMany();
    }
}