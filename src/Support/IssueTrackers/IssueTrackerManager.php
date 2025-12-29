<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Support\IssueTrackers;

use InvalidArgumentException;
use OffloadProject\Testerra\Contracts\IssueTrackerInterface;

final class IssueTrackerManager
{
    /** @var array<string, class-string<IssueTrackerInterface>> */
    private array $drivers = [
        'jira' => JiraIssueTracker::class,
        'github' => GitHubIssueTracker::class,
    ];

    /** @param array<string, mixed> $config */
    public function __construct(
        private array $config
    ) {}

    public function driver(?string $name = null): IssueTrackerInterface
    {
        $name ??= $this->config['default'] ?? 'jira';

        if (! isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Issue tracker driver [{$name}] is not supported.");
        }

        $driverConfig = $this->config['providers'][$name] ?? [];

        return new $this->drivers[$name]($driverConfig);
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function shouldQueue(): bool
    {
        return $this->config['queue'] ?? false;
    }

    public function getQueueName(): ?string
    {
        return $this->config['queue_name'] ?? null;
    }
}
