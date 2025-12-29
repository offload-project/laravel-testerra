<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Support\IssueTrackers;

use Illuminate\Support\Facades\Http;
use OffloadProject\Testerra\Contracts\IssueTrackerInterface;
use OffloadProject\Testerra\Models\Bug;

final class JiraIssueTracker implements IssueTrackerInterface
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private array $config
    ) {}

    public function createIssue(Bug $bug): ExternalIssue
    {
        $response = Http::withBasicAuth(
            $this->config['email'],
            $this->config['api_token']
        )
            ->baseUrl($this->config['host'])
            ->post('/rest/api/3/issue', [
                'fields' => [
                    'project' => ['key' => $this->config['project_key']],
                    'summary' => $bug->title,
                    'description' => $this->formatDescription($bug),
                    'issuetype' => ['name' => $this->config['issue_type'] ?? 'Bug'],
                    'priority' => ['name' => $this->mapSeverityToPriority($bug->severity->value)],
                ],
            ]);

        $response->throw();

        $data = $response->json();

        return new ExternalIssue(
            id: $data['id'],
            key: $data['key'],
            url: mb_rtrim($this->config['host'], '/').'/browse/'.$data['key'],
            provider: 'jira',
        );
    }

    public function getIssue(string $externalId): ?ExternalIssue
    {
        $response = Http::withBasicAuth(
            $this->config['email'],
            $this->config['api_token']
        )
            ->baseUrl($this->config['host'])
            ->get("/rest/api/3/issue/{$externalId}");

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        return new ExternalIssue(
            id: $data['id'],
            key: $data['key'],
            url: mb_rtrim($this->config['host'], '/').'/browse/'.$data['key'],
            provider: 'jira',
        );
    }

    public function isConfigured(): bool
    {
        return ! empty($this->config['host'])
            && ! empty($this->config['email'])
            && ! empty($this->config['api_token'])
            && ! empty($this->config['project_key']);
    }

    /**
     * Format bug description in Atlassian Document Format (ADF).
     *
     * @return array<string, mixed>
     */
    private function formatDescription(Bug $bug): array
    {
        $content = [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => $bug->description ?? 'No description provided.'],
                ],
            ],
        ];

        $bug->loadMissing(['assignment.test', 'assignment.user']);

        if ($bug->assignment?->test) {
            $content[] = [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Test: ', 'marks' => [['type' => 'strong']]],
                    ['type' => 'text', 'text' => $bug->assignment->test->title],
                ],
            ];
        }

        if ($bug->assignment?->user) {
            $content[] = [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Reported by: ', 'marks' => [['type' => 'strong']]],
                    ['type' => 'text', 'text' => $bug->assignment->user->name ?? $bug->assignment->user->email ?? 'Unknown'],
                ],
            ];
        }

        return [
            'type' => 'doc',
            'version' => 1,
            'content' => $content,
        ];
    }

    private function mapSeverityToPriority(string $severity): string
    {
        $mapping = $this->config['priority_mapping'] ?? [];

        return match ($severity) {
            'critical' => $mapping['critical'] ?? 'Highest',
            'high' => $mapping['high'] ?? 'High',
            'medium' => $mapping['medium'] ?? 'Medium',
            'low' => $mapping['low'] ?? 'Low',
            default => 'Medium',
        };
    }
}
