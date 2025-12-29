<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Support\IssueTrackers;

final readonly class ExternalIssue
{
    public function __construct(
        public string $id,
        public string $key,
        public string $url,
        public string $provider,
    ) {}
}
