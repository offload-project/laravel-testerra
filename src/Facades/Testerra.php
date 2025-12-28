<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \OffloadProject\Testerra\Models\TestGroup createGroup(string $name, ?string $description = null)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \OffloadProject\Testerra\Models\TestGroup> getGroups()
 * @method static \OffloadProject\Testerra\Models\TestGroup|null getGroup(int $id)
 * @method static \OffloadProject\Testerra\Models\TestGroup updateGroup(\OffloadProject\Testerra\Models\TestGroup $group, string $name, ?string $description = null)
 * @method static bool deleteGroup(\OffloadProject\Testerra\Models\TestGroup $group)
 * @method static \OffloadProject\Testerra\Models\Test createTest(string $title, string $instructions, array<int> $groupIds = [])
 * @method static \Illuminate\Database\Eloquent\Collection<int, \OffloadProject\Testerra\Models\Test> getTests()
 * @method static \OffloadProject\Testerra\Models\Test|null getTest(int $id)
 * @method static \OffloadProject\Testerra\Models\Test updateTest(\OffloadProject\Testerra\Models\Test $test, string $title, string $instructions)
 * @method static bool deleteTest(\OffloadProject\Testerra\Models\Test $test)
 * @method static \OffloadProject\Testerra\Models\Test addTestToGroups(\OffloadProject\Testerra\Models\Test $test, array<int> $groupIds)
 * @method static \OffloadProject\Testerra\Models\Test removeTestFromGroups(\OffloadProject\Testerra\Models\Test $test, array<int> $groupIds)
 * @method static \OffloadProject\Testerra\Models\Test syncTestGroups(\OffloadProject\Testerra\Models\Test $test, array<int> $groupIds)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \OffloadProject\Testerra\Models\Test> getTestsByGroup(\OffloadProject\Testerra\Models\TestGroup|int $group)
 * @method static void inviteTester(string $email, string $name, array<string, mixed> $metadata = [])
 * @method static void inviteFromWaitlist(mixed $entry)
 * @method static \OffloadProject\Testerra\Models\TestAssignment assignTest(object|int $user, \OffloadProject\Testerra\Models\Test|int $test)
 * @method static \Illuminate\Support\Collection<int, \OffloadProject\Testerra\Models\TestAssignment> assignTestsByGroup(object|int $user, \OffloadProject\Testerra\Models\TestGroup|int $group)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \OffloadProject\Testerra\Models\TestAssignment> getAssignmentsForUser(object|int $user)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \OffloadProject\Testerra\Models\TestAssignment> getPendingAssignments(object|int $user)
 * @method static \OffloadProject\Testerra\Models\TestAssignment markInProgress(\OffloadProject\Testerra\Models\TestAssignment $assignment)
 * @method static \OffloadProject\Testerra\Models\TestAssignment markComplete(\OffloadProject\Testerra\Models\TestAssignment $assignment)
 * @method static \OffloadProject\Testerra\Models\Bug reportBug(\OffloadProject\Testerra\Models\TestAssignment $assignment, string $title, ?string $description = null, string $severity = 'medium')
 * @method static \OffloadProject\Testerra\Models\Screenshot addScreenshot(\OffloadProject\Testerra\Models\Bug $bug, \Illuminate\Http\UploadedFile $file)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \OffloadProject\Testerra\Models\Bug> getBugsForTest(\OffloadProject\Testerra\Models\Test|int $test)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \OffloadProject\Testerra\Models\Bug> getBugsForAssignment(\OffloadProject\Testerra\Models\TestAssignment|int $assignment)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \OffloadProject\Testerra\Models\Bug> getAllBugs()
 * @method static bool isWaitlistEnabled()
 *
 * @see \OffloadProject\Testerra\Testerra
 */
final class Testerra extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'testerra';
    }
}
