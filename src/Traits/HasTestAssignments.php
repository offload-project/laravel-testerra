<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use OffloadProject\Testerra\Enums\AssignmentStatus;
use OffloadProject\Testerra\Models\Bug;
use OffloadProject\Testerra\Models\TestAssignment;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasTestAssignments
{
    /**
     * @return HasMany<TestAssignment, $this>
     */
    public function testAssignments(): HasMany
    {
        return $this->hasMany(TestAssignment::class, 'user_id');
    }

    /**
     * @return HasManyThrough<Bug, TestAssignment, $this>
     */
    public function bugs(): HasManyThrough
    {
        return $this->hasManyThrough(
            Bug::class,
            TestAssignment::class,
            'user_id',
            'assignment_id'
        );
    }

    /**
     * @return HasMany<TestAssignment, $this>
     */
    public function pendingAssignments(): HasMany
    {
        return $this->testAssignments()->where('status', AssignmentStatus::Pending);
    }

    /**
     * @return HasMany<TestAssignment, $this>
     */
    public function inProgressAssignments(): HasMany
    {
        return $this->testAssignments()->where('status', AssignmentStatus::InProgress);
    }

    /**
     * @return HasMany<TestAssignment, $this>
     */
    public function completedAssignments(): HasMany
    {
        return $this->testAssignments()->where('status', AssignmentStatus::Completed);
    }

    /**
     * @return Collection<int, TestAssignment>
     */
    public function getPendingTests(): Collection
    {
        return $this->pendingAssignments()->with('test')->get();
    }

    /**
     * @return Collection<int, TestAssignment>
     */
    public function getInProgressTests(): Collection
    {
        return $this->inProgressAssignments()->with('test')->get();
    }

    /**
     * @return Collection<int, TestAssignment>
     */
    public function getCompletedTests(): Collection
    {
        return $this->completedAssignments()->with('test')->get();
    }

    public function hasAssignment(int $testId): bool
    {
        return $this->testAssignments()->where('test_id', $testId)->exists();
    }

    public function getAssignment(int $testId): ?TestAssignment
    {
        return $this->testAssignments()->where('test_id', $testId)->first();
    }

    public function getAssignmentStats(): array
    {
        $assignments = $this->testAssignments()->get();

        return [
            'total' => $assignments->count(),
            'pending' => $assignments->where('status', AssignmentStatus::Pending)->count(),
            'in_progress' => $assignments->where('status', AssignmentStatus::InProgress)->count(),
            'completed' => $assignments->where('status', AssignmentStatus::Completed)->count(),
        ];
    }
}
