<?php

declare(strict_types=1);

use OffloadProject\Testerra\Facades\Testerra;
use OffloadProject\Testerra\Models\TestGroup;

it('can create a group', function () {
    $group = Testerra::createGroup('UI Testing', 'Frontend user interface tests');

    expect($group)->toBeInstanceOf(TestGroup::class)
        ->and($group->name)->toBe('UI Testing')
        ->and($group->description)->toBe('Frontend user interface tests');
});

it('can create a group without description', function () {
    $group = Testerra::createGroup('API Testing');

    expect($group->name)->toBe('API Testing')
        ->and($group->description)->toBeNull();
});

it('can get all groups', function () {
    Testerra::createGroup('Group 1');
    Testerra::createGroup('Group 2');
    Testerra::createGroup('Group 3');

    $groups = Testerra::getGroups();

    expect($groups)->toHaveCount(3);
});

it('can get a single group', function () {
    $created = Testerra::createGroup('UI Testing');

    $group = Testerra::getGroup($created->id);

    expect($group)->not->toBeNull()
        ->and($group->name)->toBe('UI Testing');
});

it('returns null for non-existent group', function () {
    $group = Testerra::getGroup(999);

    expect($group)->toBeNull();
});

it('can update a group', function () {
    $group = Testerra::createGroup('Old Name', 'Old description');

    $updated = Testerra::updateGroup($group, 'New Name', 'New description');

    expect($updated->name)->toBe('New Name')
        ->and($updated->description)->toBe('New description');
});

it('can delete a group', function () {
    $group = Testerra::createGroup('To Delete');

    $result = Testerra::deleteGroup($group);

    expect($result)->toBeTrue()
        ->and(Testerra::getGroup($group->id))->toBeNull();
});
