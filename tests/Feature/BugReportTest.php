<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use OffloadProject\Testerra\Enums\BugSeverity;
use OffloadProject\Testerra\Events\BugReported;
use OffloadProject\Testerra\Facades\Testerra;
use OffloadProject\Testerra\Models\Bug;
use OffloadProject\Testerra\Models\Screenshot;
use OffloadProject\Testerra\Tests\User;

it('can report a bug', function () {
    Event::fake();

    $user = User::factory()->create();
    $test = Testerra::createTest('Login Flow', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $bug = Testerra::reportBug($assignment, 'Button not clickable', 'The submit button does not respond', 'high');

    expect($bug)->toBeInstanceOf(Bug::class)
        ->and($bug->title)->toBe('Button not clickable')
        ->and($bug->description)->toBe('The submit button does not respond')
        ->and($bug->severity)->toBe(BugSeverity::High)
        ->and($bug->assignment_id)->toBe($assignment->id);

    Event::assertDispatched(BugReported::class);
});

it('can report bug with different severities', function () {
    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $lowBug = Testerra::reportBug($assignment, 'Minor issue', null, 'low');
    $criticalBug = Testerra::reportBug($assignment, 'Critical issue', null, 'critical');

    expect($lowBug->severity)->toBe(BugSeverity::Low)
        ->and($lowBug->isLow())->toBeTrue()
        ->and($criticalBug->severity)->toBe(BugSeverity::Critical)
        ->and($criticalBug->isCritical())->toBeTrue();
});

it('can add screenshot to bug', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);
    $bug = Testerra::reportBug($assignment, 'Visual bug');

    $file = UploadedFile::fake()->image('screenshot.png', 800, 600);

    $screenshot = Testerra::addScreenshot($bug, $file);

    expect($screenshot)->toBeInstanceOf(Screenshot::class)
        ->and($screenshot->bug_id)->toBe($bug->id)
        ->and($screenshot->original_filename)->toBe('screenshot.png')
        ->and($screenshot->disk)->toBe('local');

    Storage::disk('local')->assertExists($screenshot->path);
});

it('can add multiple screenshots to bug', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);
    $bug = Testerra::reportBug($assignment, 'Visual bug');

    Testerra::addScreenshot($bug, UploadedFile::fake()->image('shot1.png'));
    Testerra::addScreenshot($bug, UploadedFile::fake()->image('shot2.png'));
    Testerra::addScreenshot($bug, UploadedFile::fake()->image('shot3.png'));

    expect($bug->screenshots)->toHaveCount(3);
});

it('can get bugs for a test', function () {
    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    Testerra::reportBug($assignment, 'Bug 1');
    Testerra::reportBug($assignment, 'Bug 2');

    $bugs = Testerra::getBugsForTest($test);

    expect($bugs)->toHaveCount(2);
});

it('can get bugs for an assignment', function () {
    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    Testerra::reportBug($assignment, 'Bug 1');
    Testerra::reportBug($assignment, 'Bug 2');

    $bugs = Testerra::getBugsForAssignment($assignment);

    expect($bugs)->toHaveCount(2);
});

it('can get all bugs', function () {
    $user = User::factory()->create();
    $test1 = Testerra::createTest('Test 1', 'Instructions');
    $test2 = Testerra::createTest('Test 2', 'Instructions');
    $assignment1 = Testerra::assignTest($user, $test1);
    $assignment2 = Testerra::assignTest($user, $test2);

    Testerra::reportBug($assignment1, 'Bug 1');
    Testerra::reportBug($assignment2, 'Bug 2');

    $bugs = Testerra::getAllBugs();

    expect($bugs)->toHaveCount(2);
});

it('user can access bugs via trait', function () {
    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    Testerra::reportBug($assignment, 'Bug 1');
    Testerra::reportBug($assignment, 'Bug 2');

    expect($user->bugs)->toHaveCount(2);
});

it('deleting screenshot removes file from storage', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);
    $bug = Testerra::reportBug($assignment, 'Bug');
    $file = UploadedFile::fake()->image('screenshot.png');
    $screenshot = Testerra::addScreenshot($bug, $file);

    $path = $screenshot->path;
    Storage::disk('local')->assertExists($path);

    $screenshot->delete();

    Storage::disk('local')->assertMissing($path);
});
