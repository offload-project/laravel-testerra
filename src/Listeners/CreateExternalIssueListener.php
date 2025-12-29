<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Listeners;

use Illuminate\Support\Facades\Log;
use OffloadProject\Testerra\Events\BugReported;
use OffloadProject\Testerra\Jobs\CreateExternalIssueJob;
use OffloadProject\Testerra\Support\IssueTrackers\IssueTrackerManager;
use Throwable;

final class CreateExternalIssueListener
{
    public function __construct(
        private IssueTrackerManager $manager
    ) {}

    public function handle(BugReported $event): void
    {
        if (! $this->manager->isEnabled()) {
            return;
        }

        if (! $this->manager->driver()->isConfigured()) {
            Log::warning('Testerra: Issue tracker integration enabled but not configured.');

            return;
        }

        if ($this->manager->shouldQueue()) {
            $job = CreateExternalIssueJob::dispatch($event->bug);

            if ($queueName = $this->manager->getQueueName()) {
                $job->onQueue($queueName);
            }

            return;
        }

        $this->createIssue($event);
    }

    private function createIssue(BugReported $event): void
    {
        try {
            $externalIssue = $this->manager->driver()->createIssue($event->bug);

            $event->bug->update([
                'integration_type' => $externalIssue->provider,
                'external_id' => $externalIssue->id,
                'external_key' => $externalIssue->key,
                'external_url' => $externalIssue->url,
            ]);
        } catch (Throwable $e) {
            Log::error('Testerra: Failed to create external issue.', [
                'bug_id' => $event->bug->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
