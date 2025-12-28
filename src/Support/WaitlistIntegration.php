<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Support;

use OffloadProject\Testerra\Events\TesterInvited;

final class WaitlistIntegration
{
    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function add(string $email, string $name, array $metadata = []): mixed
    {
        $waitlistName = $this->config['name'] ?? 'testers';

        return \OffloadProject\Waitlist\Facades\Waitlist::for($waitlistName)
            ->add($name, $email, $metadata);
    }

    public function invite(mixed $entry): void
    {
        \OffloadProject\Waitlist\Facades\Waitlist::invite($entry);

        event(new TesterInvited($entry->email, $entry->name, $entry->metadata ?? []));
    }

    public function getPending(): mixed
    {
        $waitlistName = $this->config['name'] ?? 'testers';

        return \OffloadProject\Waitlist\Facades\Waitlist::for($waitlistName)->getPending();
    }

    public function getInvited(): mixed
    {
        $waitlistName = $this->config['name'] ?? 'testers';

        return \OffloadProject\Waitlist\Facades\Waitlist::for($waitlistName)->getInvited();
    }

    public function exists(string $email): bool
    {
        $waitlistName = $this->config['name'] ?? 'testers';

        return \OffloadProject\Waitlist\Facades\Waitlist::for($waitlistName)->exists($email);
    }
}
