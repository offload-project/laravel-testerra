<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TesterInvited
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $email,
        public string $name,
        public array $metadata = []
    ) {}
}
