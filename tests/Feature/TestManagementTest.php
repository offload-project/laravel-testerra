<?php

declare(strict_types=1);

use OffloadProject\Testerra\Facades\Testerra;
use OffloadProject\Testerra\Models\Test;

it('can create a test', function () {
    $test = Testerra::createTest('Login Flow', 'Step 1: Go to login page...');

    expect($test)->toBeInstanceOf(Test::class)
        ->and($test->title)->toBe('Login Flow')
        ->and($test->instructions)->toBe('Step 1: Go to login page...');
});

it('can create a test with groups', function () {
    $group1 = Testerra::createGroup('UI');
    $group2 = Testerra::createGroup('Authentication');

    $test = Testerra::createTest('Login Flow', 'Instructions...', [$group1->id, $group2->id]);

    expect($test->groups)->toHaveCount(2);
});

it('can get all tests', function () {
    Testerra::createTest('Test 1', 'Instructions 1');
    Testerra::createTest('Test 2', 'Instructions 2');

    $tests = Testerra::getTests();

    expect($tests)->toHaveCount(2);
});

it('can get a single test', function () {
    $created = Testerra::createTest('Login Flow', 'Instructions');

    $test = Testerra::getTest($created->id);

    expect($test)->not->toBeNull()
        ->and($test->title)->toBe('Login Flow');
});

it('can update a test', function () {
    $test = Testerra::createTest('Old Title', 'Old instructions');

    $updated = Testerra::updateTest($test, 'New Title', 'New instructions');

    expect($updated->title)->toBe('New Title')
        ->and($updated->instructions)->toBe('New instructions');
});

it('can delete a test', function () {
    $test = Testerra::createTest('To Delete', 'Instructions');

    $result = Testerra::deleteTest($test);

    expect($result)->toBeTrue()
        ->and(Testerra::getTest($test->id))->toBeNull();
});

it('can add test to groups', function () {
    $group = Testerra::createGroup('New Group');
    $test = Testerra::createTest('Test', 'Instructions');

    $updated = Testerra::addTestToGroups($test, [$group->id]);

    expect($updated->groups)->toHaveCount(1)
        ->and($updated->groups->first()->name)->toBe('New Group');
});

it('can remove test from groups', function () {
    $group = Testerra::createGroup('Group');
    $test = Testerra::createTest('Test', 'Instructions', [$group->id]);

    $updated = Testerra::removeTestFromGroups($test, [$group->id]);

    expect($updated->groups)->toHaveCount(0);
});

it('can sync test groups', function () {
    $group1 = Testerra::createGroup('Group 1');
    $group2 = Testerra::createGroup('Group 2');
    $group3 = Testerra::createGroup('Group 3');

    $test = Testerra::createTest('Test', 'Instructions', [$group1->id, $group2->id]);

    $updated = Testerra::syncTestGroups($test, [$group2->id, $group3->id]);

    expect($updated->groups)->toHaveCount(2)
        ->and($updated->groups->pluck('name')->toArray())->toContain('Group 2', 'Group 3')
        ->and($updated->groups->pluck('name')->toArray())->not->toContain('Group 1');
});

it('can get tests by group', function () {
    $group = Testerra::createGroup('UI');

    Testerra::createTest('Test 1', 'Instructions', [$group->id]);
    Testerra::createTest('Test 2', 'Instructions', [$group->id]);
    Testerra::createTest('Test 3', 'Instructions');

    $tests = Testerra::getTestsByGroup($group);

    expect($tests)->toHaveCount(2);
});
