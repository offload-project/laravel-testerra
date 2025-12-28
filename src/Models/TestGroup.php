<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class TestGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function getTable(): string
    {
        return config('testerra.table_prefix', 'testerra_').'groups';
    }

    /**
     * @return BelongsToMany<Test, $this>
     */
    public function tests(): BelongsToMany
    {
        return $this->belongsToMany(
            Test::class,
            config('testerra.table_prefix', 'testerra_').'test_group',
            'group_id',
            'test_id'
        );
    }
}
