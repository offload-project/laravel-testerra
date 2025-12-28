<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $title
 * @property string $instructions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Test extends Model
{
    protected $fillable = [
        'title',
        'instructions',
    ];

    public function getTable(): string
    {
        return config('testerra.table_prefix', 'testerra_').'tests';
    }

    /**
     * @return BelongsToMany<TestGroup, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            TestGroup::class,
            config('testerra.table_prefix', 'testerra_').'test_group',
            'test_id',
            'group_id'
        );
    }

    /**
     * @return HasMany<TestAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TestAssignment::class, 'test_id');
    }

    /**
     * @return HasManyThrough<Bug, TestAssignment, $this>
     */
    public function bugs(): HasManyThrough
    {
        return $this->hasManyThrough(
            Bug::class,
            TestAssignment::class,
            'test_id',
            'assignment_id'
        );
    }
}
