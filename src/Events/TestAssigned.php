<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OffloadProject\Testerra\Models\TestAssignment;

final class TestAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TestAssignment $assignment
    ) {}
}
