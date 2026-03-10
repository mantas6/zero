<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read Task|null $task
 * @property Carbon|null $started_at
 * @property Carbon|null $stopped_at
 */
class TimeEntry extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Scope to entries created today.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWhereToday(Builder $query): Builder
    {
        return $query->whereDate('started_at', now()->toDateString());
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'stopped_at' => 'datetime',
        ];
    }
}
