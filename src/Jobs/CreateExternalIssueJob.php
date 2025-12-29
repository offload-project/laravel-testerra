<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OffloadProject\Testerra\Models\Bug;
use OffloadProject\Testerra\Support\IssueTrackers\IssueTrackerManager;
use Throwable;

final class CreateExternalIssueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public Bug $bug
    ) {}

    public function handle(IssueTrackerManager $manager): void
    {
        if ($this->bug->external_id !== null) {
            return;
        }

        $externalIssue = $manager->driver()->createIssue($this->bug);

        $this->bug->update([
            'integration_type' => $externalIssue->provider,
            'external_id' => $externalIssue->id,
            'external_key' => $externalIssue->key,
            'external_url' => $externalIssue->url,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Testerra: Failed to create external issue after retries.', [
            'bug_id' => $this->bug->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
