<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OffloadProject\Testerra\Enums\AssignmentStatus;

/**
 * @property int $id
 * @property int $user_id
 * @property int $test_id
 * @property AssignmentStatus $status
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class TestAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'test_id',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'status' => AssignmentStatus::class,
        'completed_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('testerra.table_prefix', 'testerra_').'assignments';
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        /** @var class-string<Model> $userModel */
        $userModel = config('testerra.user_model', \Illuminate\Foundation\Auth\User::class);

        return $this->belongsTo($userModel);
    }

    /**
     * @return BelongsTo<Test, $this>
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    /**
     * @return HasMany<Bug, $this>
     */
    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class, 'assignment_id');
    }

    public function isPending(): bool
    {
        return $this->status === AssignmentStatus::Pending;
    }

    public function isInProgress(): bool
    {
        return $this->status === AssignmentStatus::InProgress;
    }

    public function isCompleted(): bool
    {
        return $this->status === AssignmentStatus::Completed;
    }

    public function markAsPending(): void
    {
        $this->update([
            'status' => AssignmentStatus::Pending,
            'completed_at' => null,
        ]);
    }

    public function markAsInProgress(): void
    {
        $this->update([
            'status' => AssignmentStatus::InProgress,
            'completed_at' => null,
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => AssignmentStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
