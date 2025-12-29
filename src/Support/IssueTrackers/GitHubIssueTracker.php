<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Support\IssueTrackers;

use Illuminate\Support\Facades\Http;
use OffloadProject\Testerra\Contracts\IssueTrackerInterface;
use OffloadProject\Testerra\Models\Bug;

final class GitHubIssueTracker implements IssueTrackerInterface
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private array $config
    ) {}

    public function createIssue(Bug $bug): ExternalIssue
    {
        $response = Http::withToken($this->config['token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->post($this->getApiUrl('/issues'), [
                'title' => $bug->title,
                'body' => $this->formatBody($bug),
                'labels' => $this->getLabels($bug),
            ]);

        $response->throw();

        $data = $response->json();

        return new ExternalIssue(
            id: (string) $data['id'],
            key: (string) $data['number'],
            url: $data['html_url'],
            provider: 'github',
        );
    }

    public function getIssue(string $externalId): ?ExternalIssue
    {
        $response = Http::withToken($this->config['token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->get($this->getApiUrl("/issues/{$externalId}"));

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        return new ExternalIssue(
            id: (string) $data['id'],
            key: (string) $data['number'],
            url: $data['html_url'],
            provider: 'github',
        );
    }

    public function isConfigured(): bool
    {
        return ! empty($this->config['token'])
            && ! empty($this->config['owner'])
            && ! empty($this->config['repo']);
    }

    private function getApiUrl(string $path): string
    {
        $owner = $this->config['owner'];
        $repo = $this->config['repo'];

        return "https://api.github.com/repos/{$owner}/{$repo}".mb_ltrim($path, '/');
    }

    private function formatBody(Bug $bug): string
    {
        $body = $bug->description ?? 'No description provided.';

        $bug->loadMissing(['assignment.test', 'assignment.user']);

        if ($bug->assignment?->test) {
            $body .= "\n\n**Test:** {$bug->assignment->test->title}";
        }

        if ($bug->assignment?->user) {
            $name = $bug->assignment->user->name ?? $bug->assignment->user->email ?? 'Unknown';
            $body .= "\n**Reported by:** {$name}";
        }

        $body .= "\n\n---\n_Created via Testerra_";

        return $body;
    }

    /**
     * @return array<int, string>
     */
    private function getLabels(Bug $bug): array
    {
        $labels = $this->config['labels'] ?? ['bug'];

        $severityLabels = $this->config['severity_labels'] ?? [];
        if (isset($severityLabels[$bug->severity->value])) {
            $labels[] = $severityLabels[$bug->severity->value];
        }

        return $labels;
    }
}
