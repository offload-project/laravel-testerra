<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Contracts;

use OffloadProject\Testerra\Models\Bug;
use OffloadProject\Testerra\Support\IssueTrackers\ExternalIssue;

interface IssueTrackerInterface
{
    /**
     * Create an issue in the external tracker.
     */
    public function createIssue(Bug $bug): ExternalIssue;

    /**
     * Get the issue from the external tracker.
     */
    public function getIssue(string $externalId): ?ExternalIssue;

    /**
     * Check if the tracker is properly configured.
     */
    public function isConfigured(): bool;
}
