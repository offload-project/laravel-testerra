<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use OffloadProject\Testerra\Enums\BugSeverity;

/**
 * @property int $id
 * @property int $assignment_id
 * @property string $title
 * @property string|null $description
 * @property BugSeverity $severity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Bug extends Model
{
    protected $fillable = [
        'assignment_id',
        'title',
        'description',
        'severity',
    ];

    protected $casts = [
        'severity' => BugSeverity::class,
    ];

    public function getTable(): string
    {
        return config('testerra.table_prefix', 'testerra_').'bugs';
    }

    /**
     * @return BelongsTo<TestAssignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TestAssignment::class, 'assignment_id');
    }

    /**
     * @return HasOneThrough<Test, TestAssignment, $this>
     */
    public function test(): HasOneThrough
    {
        return $this->hasOneThrough(
            Test::class,
            TestAssignment::class,
            'id',
            'id',
            'assignment_id',
            'test_id'
        );
    }

    /**
     * @return HasMany<Screenshot, $this>
     */
    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class, 'bug_id');
    }

    public function isLow(): bool
    {
        return $this->severity === BugSeverity::Low;
    }

    public function isMedium(): bool
    {
        return $this->severity === BugSeverity::Medium;
    }

    public function isHigh(): bool
    {
        return $this->severity === BugSeverity::High;
    }

    public function isCritical(): bool
    {
        return $this->severity === BugSeverity::Critical;
    }
}
