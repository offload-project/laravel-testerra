<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use OffloadProject\Testerra\Enums\AssignmentStatus;
use OffloadProject\Testerra\Events\TestAssigned;
use OffloadProject\Testerra\Events\TestCompleted;
use OffloadProject\Testerra\Facades\Testerra;
use OffloadProject\Testerra\Models\TestAssignment;
use OffloadProject\Testerra\Tests\User;

it('can assign a test to a user', function () {
    Event::fake();

    $user = User::factory()->create();
    $test = Testerra::createTest('Login Flow', 'Instructions');

    $assignment = Testerra::assignTest($user, $test);

    expect($assignment)->toBeInstanceOf(TestAssignment::class)
        ->and($assignment->user_id)->toBe($user->id)
        ->and($assignment->test_id)->toBe($test->id)
        ->and($assignment->status)->toBe(AssignmentStatus::Pending);

    Event::assertDispatched(TestAssigned::class);
});

it('can assign test using test id', function () {
    $user = User::factory()->create();
    $test = Testerra::createTest('Login Flow', 'Instructions');

    $assignment = Testerra::assignTest($user, $test->id);

    expect($assignment->test_id)->toBe($test->id);
});

it('does not create duplicate assignments', function () {
    $user = User::factory()->create();
    $test = Testerra::createTest('Login Flow', 'Instructions');

    $assignment1 = Testerra::assignTest($user, $test);
    $assignment2 = Testerra::assignTest($user, $test);

    expect($assignment1->id)->toBe($assignment2->id)
        ->and(TestAssignment::count())->toBe(1);
});

it('can assign all tests in a group to a user', function () {
    $user = User::factory()->create();
    $group = Testerra::createGroup('UI Tests');

    Testerra::createTest('Test 1', 'Instructions', [$group->id]);
    Testerra::createTest('Test 2', 'Instructions', [$group->id]);
    Testerra::createTest('Test 3', 'Instructions', [$group->id]);

    $assignments = Testerra::assignTestsByGroup($user, $group);

    expect($assignments)->toHaveCount(3);
});

it('can get assignments for a user', function () {
    $user = User::factory()->create();
    $test1 = Testerra::createTest('Test 1', 'Instructions');
    $test2 = Testerra::createTest('Test 2', 'Instructions');

    Testerra::assignTest($user, $test1);
    Testerra::assignTest($user, $test2);

    $assignments = Testerra::getAssignmentsForUser($user);

    expect($assignments)->toHaveCount(2);
});

it('can get pending assignments for a user', function () {
    $user = User::factory()->create();
    $test1 = Testerra::createTest('Test 1', 'Instructions');
    $test2 = Testerra::createTest('Test 2', 'Instructions');

    $assignment1 = Testerra::assignTest($user, $test1);
    Testerra::assignTest($user, $test2);
    Testerra::markComplete($assignment1);

    $pending = Testerra::getPendingAssignments($user);

    expect($pending)->toHaveCount(1)
        ->and($pending->first()->test_id)->toBe($test2->id);
});

it('can mark assignment as in progress', function () {
    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $updated = Testerra::markInProgress($assignment);

    expect($updated->status)->toBe(AssignmentStatus::InProgress);
});

it('can mark assignment as complete', function () {
    Event::fake();

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $updated = Testerra::markComplete($assignment);

    expect($updated->status)->toBe(AssignmentStatus::Completed)
        ->and($updated->completed_at)->not->toBeNull();

    Event::assertDispatched(TestCompleted::class);
});

it('user can access assignments via trait', function () {
    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    Testerra::assignTest($user, $test);

    expect($user->testAssignments)->toHaveCount(1)
        ->and($user->hasAssignment($test->id))->toBeTrue();
});

it('user can get assignment stats', function () {
    $user = User::factory()->create();
    $test1 = Testerra::createTest('Test 1', 'Instructions');
    $test2 = Testerra::createTest('Test 2', 'Instructions');
    $test3 = Testerra::createTest('Test 3', 'Instructions');

    $assignment1 = Testerra::assignTest($user, $test1);
    $assignment2 = Testerra::assignTest($user, $test2);
    Testerra::assignTest($user, $test3);

    Testerra::markComplete($assignment1);
    Testerra::markInProgress($assignment2);

    $stats = $user->getAssignmentStats();

    expect($stats['total'])->toBe(3)
        ->and($stats['pending'])->toBe(1)
        ->and($stats['in_progress'])->toBe(1)
        ->and($stats['completed'])->toBe(1);
});
