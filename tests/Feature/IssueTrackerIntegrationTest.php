<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use OffloadProject\Testerra\Facades\Testerra;
use OffloadProject\Testerra\Jobs\CreateExternalIssueJob;
use OffloadProject\Testerra\Tests\User;

beforeEach(function () {
    config()->set('testerra.issue_tracker.enabled', false);
});

it('creates external issue when integration is enabled', function () {
    Http::fake([
        'https://test.atlassian.net/*' => Http::response([
            'id' => '12345',
            'key' => 'PROJ-123',
        ], 201),
    ]);

    config()->set('testerra.issue_tracker.enabled', true);
    config()->set('testerra.issue_tracker.providers.jira', [
        'host' => 'https://test.atlassian.net',
        'email' => 'test@example.com',
        'api_token' => 'token',
        'project_key' => 'PROJ',
    ]);

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $bug = Testerra::reportBug($assignment, 'Test bug', 'Description', 'high');

    expect($bug->fresh())
        ->external_id->toBe('12345')
        ->external_key->toBe('PROJ-123')
        ->integration_type->toBe('jira')
        ->external_url->toBe('https://test.atlassian.net/browse/PROJ-123');
});

it('does not create external issue when integration is disabled', function () {
    Http::fake();

    config()->set('testerra.issue_tracker.enabled', false);

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $bug = Testerra::reportBug($assignment, 'Test bug');

    Http::assertNothingSent();
    expect($bug->external_id)->toBeNull();
});

it('does not fail bug creation when external api fails', function () {
    Http::fake([
        '*' => Http::response([], 500),
    ]);

    config()->set('testerra.issue_tracker.enabled', true);
    config()->set('testerra.issue_tracker.providers.jira', [
        'host' => 'https://test.atlassian.net',
        'email' => 'test@example.com',
        'api_token' => 'token',
        'project_key' => 'PROJ',
    ]);

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $bug = Testerra::reportBug($assignment, 'Test bug');

    expect($bug->id)->not->toBeNull()
        ->and($bug->external_id)->toBeNull();
});

it('queues issue creation when queue option is enabled', function () {
    Queue::fake();

    config()->set('testerra.issue_tracker.enabled', true);
    config()->set('testerra.issue_tracker.queue', true);
    config()->set('testerra.issue_tracker.providers.jira', [
        'host' => 'https://test.atlassian.net',
        'email' => 'test@example.com',
        'api_token' => 'token',
        'project_key' => 'PROJ',
    ]);

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    Testerra::reportBug($assignment, 'Test bug');

    Queue::assertPushed(CreateExternalIssueJob::class);
});

it('does not create issue when tracker is not configured', function () {
    Http::fake();

    config()->set('testerra.issue_tracker.enabled', true);
    config()->set('testerra.issue_tracker.providers.jira', [
        'host' => null,
        'email' => null,
        'api_token' => null,
        'project_key' => null,
    ]);

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $bug = Testerra::reportBug($assignment, 'Test bug');

    Http::assertNothingSent();
    expect($bug->external_id)->toBeNull();
});

it('bug has external issue helper methods', function () {
    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $bug = Testerra::reportBug($assignment, 'Test bug');

    expect($bug->hasExternalIssue())->toBeFalse()
        ->and($bug->getExternalUrl())->toBeNull();

    $bug->update([
        'integration_type' => 'jira',
        'external_id' => '12345',
        'external_key' => 'PROJ-123',
        'external_url' => 'https://test.atlassian.net/browse/PROJ-123',
    ]);

    expect($bug->hasExternalIssue())->toBeTrue()
        ->and($bug->getExternalUrl())->toBe('https://test.atlassian.net/browse/PROJ-123');
});

it('maps bug severity to jira priority', function () {
    Http::fake([
        'https://test.atlassian.net/*' => Http::response([
            'id' => '12345',
            'key' => 'PROJ-123',
        ], 201),
    ]);

    config()->set('testerra.issue_tracker.enabled', true);
    config()->set('testerra.issue_tracker.providers.jira', [
        'host' => 'https://test.atlassian.net',
        'email' => 'test@example.com',
        'api_token' => 'token',
        'project_key' => 'PROJ',
        'priority_mapping' => [
            'critical' => 'Highest',
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
        ],
    ]);

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    Testerra::reportBug($assignment, 'Critical bug', 'Description', 'critical');

    Http::assertSent(function ($request) {
        return $request['fields']['priority']['name'] === 'Highest';
    });
});

it('creates github issue when github driver is configured', function () {
    Http::fake([
        'https://api.github.com/*' => Http::response([
            'id' => 98765,
            'number' => 42,
            'html_url' => 'https://github.com/owner/repo/issues/42',
        ], 201),
    ]);

    config()->set('testerra.issue_tracker.enabled', true);
    config()->set('testerra.issue_tracker.default', 'github');
    config()->set('testerra.issue_tracker.providers.github', [
        'token' => 'ghp_test_token',
        'owner' => 'owner',
        'repo' => 'repo',
        'labels' => ['bug'],
    ]);

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    $bug = Testerra::reportBug($assignment, 'GitHub bug', 'Description', 'high');

    expect($bug->fresh())
        ->external_id->toBe('98765')
        ->external_key->toBe('42')
        ->integration_type->toBe('github')
        ->external_url->toBe('https://github.com/owner/repo/issues/42');
});

it('applies severity labels to github issues', function () {
    Http::fake([
        'https://api.github.com/*' => Http::response([
            'id' => 98765,
            'number' => 42,
            'html_url' => 'https://github.com/owner/repo/issues/42',
        ], 201),
    ]);

    config()->set('testerra.issue_tracker.enabled', true);
    config()->set('testerra.issue_tracker.default', 'github');
    config()->set('testerra.issue_tracker.providers.github', [
        'token' => 'ghp_test_token',
        'owner' => 'owner',
        'repo' => 'repo',
        'labels' => ['bug'],
        'severity_labels' => [
            'critical' => 'priority: critical',
        ],
    ]);

    $user = User::factory()->create();
    $test = Testerra::createTest('Test', 'Instructions');
    $assignment = Testerra::assignTest($user, $test);

    Testerra::reportBug($assignment, 'Critical bug', 'Description', 'critical');

    Http::assertSent(function ($request) {
        $labels = $request['labels'] ?? [];

        return in_array('bug', $labels) && in_array('priority: critical', $labels);
    });
});
