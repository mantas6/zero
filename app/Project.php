<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Collection<int, Task> $tasks
 * @property int $ext_id
 * @property string $name
 * @property bool $active
 * @property bool $billable
 * @property string|null $color
 * @property string|null $client_name
 * @property array<int, string> $tags
 */
class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'ext_id'];

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Scope to only active projects.
     *
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeWhereActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'billable' => 'boolean',
            'tags' => 'array',
        ];
    }
}
