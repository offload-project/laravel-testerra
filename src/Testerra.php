<?php

declare(strict_types=1);

namespace OffloadProject\Testerra;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as BaseCollection;
use OffloadProject\InviteOnly\Models\Invitation;
use OffloadProject\Testerra\Enums\AssignmentStatus;
use OffloadProject\Testerra\Enums\BugSeverity;
use OffloadProject\Testerra\Events\BugReported;
use OffloadProject\Testerra\Events\TestAssigned;
use OffloadProject\Testerra\Events\TestCompleted;
use OffloadProject\Testerra\Models\Bug;
use OffloadProject\Testerra\Models\Screenshot;
use OffloadProject\Testerra\Models\Test;
use OffloadProject\Testerra\Models\TestAssignment;
use OffloadProject\Testerra\Models\TestGroup;
use OffloadProject\Testerra\Support\InviteOnlyIntegration;
use OffloadProject\Testerra\Support\WaitlistIntegration;
use RuntimeException;

final class Testerra
{
    /** @var array<string, mixed> */
    private array $config;

    private ?WaitlistIntegration $waitlist = null;

    private InviteOnlyIntegration $inviteOnly;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->inviteOnly = new InviteOnlyIntegration;

        if ($this->isWaitlistEnabled()) {
            $this->waitlist = new WaitlistIntegration($config['waitlist']);
        }
    }

    // ==================== Groups ====================

    public function createGroup(string $name, ?string $description = null): TestGroup
    {
        return TestGroup::create([
            'name' => $name,
            'description' => $description,
        ]);
    }

    /**
     * @return Collection<int, TestGroup>
     */
    public function getGroups(): Collection
    {
        return TestGroup::all();
    }

    public function getGroup(int $id): ?TestGroup
    {
        return TestGroup::find($id);
    }

    public function updateGroup(TestGroup $group, string $name, ?string $description = null): TestGroup
    {
        $group->update([
            'name' => $name,
            'description' => $description,
        ]);

        return $group->fresh();
    }

    public function deleteGroup(TestGroup $group): bool
    {
        return $group->delete();
    }

    // ==================== Tests ====================

    /**
     * @param  array<int>  $groupIds
     */
    public function createTest(string $title, string $instructions, array $groupIds = []): Test
    {
        $test = Test::create([
            'title' => $title,
            'instructions' => $instructions,
        ]);

        if (! empty($groupIds)) {
            $test->groups()->attach($groupIds);
        }

        return $test->load('groups');
    }

    /**
     * @return Collection<int, Test>
     */
    public function getTests(): Collection
    {
        return Test::with('groups')->get();
    }

    public function getTest(int $id): ?Test
    {
        return Test::with('groups')->find($id);
    }

    public function updateTest(Test $test, string $title, string $instructions): Test
    {
        $test->update([
            'title' => $title,
            'instructions' => $instructions,
        ]);

        return $test->fresh();
    }

    public function deleteTest(Test $test): bool
    {
        return $test->delete();
    }

    /**
     * @param  array<int>  $groupIds
     */
    public function addTestToGroups(Test $test, array $groupIds): Test
    {
        $test->groups()->attach($groupIds);

        return $test->load('groups');
    }

    /**
     * @param  array<int>  $groupIds
     */
    public function removeTestFromGroups(Test $test, array $groupIds): Test
    {
        $test->groups()->detach($groupIds);

        return $test->load('groups');
    }

    /**
     * @param  array<int>  $groupIds
     */
    public function syncTestGroups(Test $test, array $groupIds): Test
    {
        $test->groups()->sync($groupIds);

        return $test->load('groups');
    }

    /**
     * @return Collection<int, Test>
     */
    public function getTestsByGroup(TestGroup|int $group): Collection
    {
        $groupId = $group instanceof TestGroup ? $group->id : $group;

        return Test::whereHas('groups', function ($query) use ($groupId) {
            $query->where('group_id', $groupId);
        })->get();
    }

    // ==================== Invitations ====================

    /**
     * Invite a tester to a test group.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function inviteTester(string $email, Model $invitable, array $metadata = []): Invitation
    {
        return $this->inviteOnly->invite($email, $invitable, $metadata);
    }

    /**
     * Accept a tester invitation.
     */
    public function acceptInvitation(string $token, Model $user): Invitation
    {
        return $this->inviteOnly->accept($token, $user);
    }

    /**
     * Decline a tester invitation.
     */
    public function declineInvitation(string $token): Invitation
    {
        return $this->inviteOnly->decline($token);
    }

    /**
     * Cancel a tester invitation.
     */
    public function cancelInvitation(Invitation $invitation): Invitation
    {
        return $this->inviteOnly->cancel($invitation);
    }

    /**
     * Get pending invitations for an invitable model.
     *
     * @return Collection<int, Invitation>
     */
    public function getPendingInvitations(Model $invitable): Collection
    {
        return $this->inviteOnly->pending($invitable);
    }

    /**
     * Find an invitation by token.
     */
    public function findInvitation(string $token): ?Invitation
    {
        return $this->inviteOnly->find($token);
    }

    /**
     * Find an invitation by email for an invitable.
     */
    public function findInvitationByEmail(string $email, Model $invitable): ?Invitation
    {
        return $this->inviteOnly->findByEmail($email, $invitable);
    }

    /**
     * Check if an email has a pending invitation.
     */
    public function hasPendingInvitation(string $email, Model $invitable): bool
    {
        return $this->inviteOnly->hasPendingInvitation($email, $invitable);
    }

    /**
     * Resend a tester invitation.
     */
    public function resendInvitation(Invitation $invitation): Invitation
    {
        return $this->inviteOnly->resend($invitation);
    }

    public function inviteFromWaitlist(mixed $entry, Model $invitable): Invitation
    {
        if (! $this->isWaitlistEnabled() || ! $this->waitlist) {
            throw new RuntimeException('Waitlist integration is not enabled.');
        }

        $this->waitlist->invite($entry);

        return $this->inviteTester($entry->email, $invitable, [
            'name' => $entry->name,
            ...$entry->metadata ?? [],
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function addToWaitlist(string $email, string $name, array $metadata = []): mixed
    {
        if (! $this->isWaitlistEnabled() || ! $this->waitlist) {
            throw new RuntimeException('Waitlist integration is not enabled.');
        }

        return $this->waitlist->add($email, $name, $metadata);
    }

    public function isWaitlistEnabled(): bool
    {
        return ($this->config['waitlist']['enabled'] ?? false)
            && class_exists(\OffloadProject\Waitlist\Facades\Waitlist::class);
    }

    // ==================== Assignments ====================

    public function assignTest(object|int $user, Test|int $test): TestAssignment
    {
        $testId = $test instanceof Test ? $test->id : $test;
        $userId = $this->resolveUserId($user);

        $assignment = TestAssignment::firstOrCreate(
            ['user_id' => $userId, 'test_id' => $testId],
            ['status' => AssignmentStatus::Pending]
        );

        event(new TestAssigned($assignment));

        return $assignment->load('test');
    }

    /**
     * @return BaseCollection<int, TestAssignment>
     */
    public function assignTestsByGroup(object|int $user, TestGroup|int $group): BaseCollection
    {
        $tests = $this->getTestsByGroup($group);
        /** @var BaseCollection<int, TestAssignment> $assignments */
        $assignments = collect();

        foreach ($tests as $test) {
            $assignments->push($this->assignTest($user, $test));
        }

        return $assignments;
    }

    /**
     * @return Collection<int, TestAssignment>
     */
    public function getAssignmentsForUser(object|int $user): Collection
    {
        $userId = $this->resolveUserId($user);

        return TestAssignment::where('user_id', $userId)
            ->with(['test', 'bugs'])
            ->get();
    }

    /**
     * @return Collection<int, TestAssignment>
     */
    public function getPendingAssignments(object|int $user): Collection
    {
        $userId = $this->resolveUserId($user);

        return TestAssignment::where('user_id', $userId)
            ->where('status', AssignmentStatus::Pending)
            ->with('test')
            ->get();
    }

    public function markInProgress(TestAssignment $assignment): TestAssignment
    {
        $assignment->markAsInProgress();

        return $assignment->fresh();
    }

    public function markComplete(TestAssignment $assignment): TestAssignment
    {
        $assignment->markAsCompleted();

        event(new TestCompleted($assignment));

        return $assignment->fresh();
    }

    // ==================== Bug Reporting ====================

    public function reportBug(
        TestAssignment $assignment,
        string $title,
        ?string $description = null,
        string $severity = 'medium'
    ): Bug {
        $bug = Bug::create([
            'assignment_id' => $assignment->id,
            'title' => $title,
            'description' => $description,
            'severity' => BugSeverity::from($severity),
        ]);

        event(new BugReported($bug));

        return $bug;
    }

    public function addScreenshot(Bug $bug, UploadedFile $file): Screenshot
    {
        $disk = $this->config['screenshots']['disk'] ?? 'local';
        $path = $this->config['screenshots']['path'] ?? 'testerra/screenshots';

        $storedPath = $file->store($path, $disk);

        return Screenshot::create([
            'bug_id' => $bug->id,
            'path' => $storedPath,
            'disk' => $disk,
            'original_filename' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * @return Collection<int, Bug>
     */
    public function getBugsForTest(Test|int $test): Collection
    {
        $testId = $test instanceof Test ? $test->id : $test;

        return Bug::whereHas('assignment', function ($query) use ($testId) {
            $query->where('test_id', $testId);
        })->with(['assignment', 'screenshots'])->get();
    }

    /**
     * @return Collection<int, Bug>
     */
    public function getBugsForAssignment(TestAssignment|int $assignment): Collection
    {
        $assignmentId = $assignment instanceof TestAssignment ? $assignment->id : $assignment;

        return Bug::where('assignment_id', $assignmentId)
            ->with('screenshots')
            ->get();
    }

    /**
     * @return Collection<int, Bug>
     */
    public function getAllBugs(): Collection
    {
        return Bug::with(['assignment.test', 'assignment.user', 'screenshots'])->get();
    }

    /**
     * Resolve a user to its ID.
     */
    private function resolveUserId(object|int $user): int
    {
        if (is_int($user)) {
            return $user;
        }

        /** @var object{id: int} $user */
        return $user->id;
    }
}
