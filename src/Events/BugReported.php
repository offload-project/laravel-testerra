<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OffloadProject\Testerra\Models\Bug;

final class BugReported
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Bug $bug
    ) {}
}
