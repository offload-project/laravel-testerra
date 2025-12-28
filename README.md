<p align="center">
    <a href="https://packagist.org/packages/offload-project/laravel-testerra"><img src="https://img.shields.io/packagist/v/offload-project/laravel-testerra.svg?style=flat-square" alt="Latest Version on Packagist"></a>
    <a href="https://github.com/offload-project/laravel-testerra/actions"><img src="https://img.shields.io/github/actions/workflow/status/offload-project/laravel-testerra/tests.yml?branch=main&style=flat-square" alt="GitHub Tests Action Status"></a>
    <a href="https://packagist.org/packages/offload-project/laravel-testerra"><img src="https://img.shields.io/packagist/dt/offload-project/laravel-testerra.svg?style=flat-square" alt="Total Downloads"></a>
</p>

# Laravel Testerra

A Laravel package for managing beta testing programs with test assignments, bug reporting, and tester invitations.

## Features

- **Test Management**: Create and organize tests into groups
- **Tester Invitations**: Invite testers via email with token-based secure links (powered
  by [laravel-invite-only](https://github.com/offload-project/laravel-invite-only))
- **Test Assignments**: Assign tests to users individually or by group
- **Bug Reporting**: Track bugs with severity levels and screenshot attachments
- **Waitlist Integration**: Optional integration
  with [laravel-waitlist](https://github.com/offload-project/laravel-waitlist)
- **Event-Driven**: Events fired for key actions (invitations, assignments, completions, bug reports)

## Requirements

- PHP 8.4+
- Laravel 11.0 or 12.0

## Installation

```bash
composer require offload-project/laravel-testerra
```

Publish the configuration and migrations:

```bash
php artisan vendor:publish --tag="testerra-config"
php artisan vendor:publish --tag="testerra-migrations"
php artisan migrate
```

Since this package uses `laravel-invite-only` for invitations, you should also publish its config:

```bash
php artisan vendor:publish --tag="invite-only-config"
php artisan vendor:publish --tag="invite-only-migrations"
php artisan migrate
```

## Configuration

After publishing, configure the package in `config/testerra.php`:

```php
return [
    'table_prefix' => 'testerra_',
    'user_model' => App\Models\User::class,
    'screenshots' => [
        'disk' => env('TESTERRA_SCREENSHOT_DISK', 'local'),
        'path' => 'testerra/screenshots',
    ],
    'waitlist' => [
        'enabled' => false,
        'name' => 'testers',
    ],
];
```

## Usage

### Using the Facade

```php
use OffloadProject\Testerra\Facades\Testerra;
```

### Managing Test Groups

```php
// Create a group
$group = Testerra::createGroup('Mobile App Tests', 'Tests for the mobile application');

// Get all groups
$groups = Testerra::getGroups();

// Update a group
Testerra::updateGroup($group, 'Updated Name', 'Updated description');

// Delete a group
Testerra::deleteGroup($group);
```

### Managing Tests

```php
// Create a test
$test = Testerra::createTest(
    'Login Flow',
    'Test the login process with valid and invalid credentials',
    [$group->id] // Optional: assign to groups
);

// Get all tests
$tests = Testerra::getTests();

// Add test to groups
Testerra::addTestToGroups($test, [$group1->id, $group2->id]);

// Get tests by group
$testsInGroup = Testerra::getTestsByGroup($group);
```

### Inviting Testers

Invitations are handled via the `laravel-invite-only` package:

```php
// Invite a tester to a test group
$invitation = Testerra::inviteTester('tester@example.com', $group, [
    'role' => 'beta-tester',
    'metadata' => ['source' => 'website'],
]);

// Accept an invitation
$invitation = Testerra::acceptInvitation($token, $user);

// Decline an invitation
$invitation = Testerra::declineInvitation($token);

// Cancel an invitation
$invitation = Testerra::cancelInvitation($invitation);

// Get pending invitations for a group
$pending = Testerra::getPendingInvitations($group);

// Check if email has pending invitation
$hasPending = Testerra::hasPendingInvitation('tester@example.com', $group);

// Resend an invitation
Testerra::resendInvitation($invitation);
```

### Assigning Tests

```php
// Assign a single test to a user
$assignment = Testerra::assignTest($user, $test);

// Assign all tests in a group to a user
$assignments = Testerra::assignTestsByGroup($user, $group);

// Get assignments for a user
$assignments = Testerra::getAssignmentsForUser($user);

// Get pending assignments
$pending = Testerra::getPendingAssignments($user);

// Update assignment status
Testerra::markInProgress($assignment);
Testerra::markComplete($assignment);
```

### Reporting Bugs

```php
// Report a bug
$bug = Testerra::reportBug(
    $assignment,
    'Login button not working',
    'When clicking the login button, nothing happens',
    'high' // Severity: low, medium, high, critical
);

// Add screenshots
$screenshot = Testerra::addScreenshot($bug, $uploadedFile);

// Get bugs
$bugsForTest = Testerra::getBugsForTest($test);
$bugsForAssignment = Testerra::getBugsForAssignment($assignment);
$allBugs = Testerra::getAllBugs();
```

### User Trait

Add the `HasTestAssignments` trait to your User model:

```php
use OffloadProject\Testerra\Traits\HasTestAssignments;

class User extends Authenticatable
{
    use HasTestAssignments;
}
```

This provides convenient methods:

```php
$user->testAssignments;
$user->pendingAssignments();
$user->completedAssignments();
$user->bugs;
$user->getAssignmentStats();
```

## Events

The package dispatches the following events:

- `TesterInvited` - When a tester is invited
- `TestAssigned` - When a test is assigned to a user
- `TestCompleted` - When a test assignment is marked complete
- `BugReported` - When a bug is reported

## Waitlist Integration

To use the waitlist integration, install the [laravel-waitlist](https://github.com/offload-project/laravel-waitlist)
package and enable it in your config:

```php
'waitlist' => [
    'enabled' => true,
    'name' => 'testers',
],
```

Then you can:

```php
// Add someone to the waitlist
Testerra::addToWaitlist('user@example.com', 'John Doe', ['source' => 'landing-page']);

// Invite from waitlist
Testerra::inviteFromWaitlist($waitlistEntry, $group);
```

## Testing

```bash
composer test
```

## Code Style

```bash
composer pint
```

## Static Analysis

```bash
composer analyse
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
