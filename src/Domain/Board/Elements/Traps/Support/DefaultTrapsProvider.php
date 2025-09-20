<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Traps\Support;

use Domain\Board\Elements\Traps\Constants\TrapStatus;
use Domain\Board\Elements\Traps\Constants\TrapType;
use Domain\Board\Elements\Traps\DataObjects\Trap;
use Illuminate\Support\Collection;

class DefaultTrapsProvider
{
    public static function get(): Collection
    {
        return new Collection([
            new Trap(
                'Falling Block',
                TrapType::BLOCK,
                TrapStatus::ARMED,
                true
            ),
            new Trap(
                'Pit Trap',
                TrapType::PIT,
                TrapStatus::ARMED,
                false
            ),
            new Trap(
                'Spear Trap',
                TrapType::SPEAR,
                TrapStatus::ARMED,
                true
            ),
            new Trap(
                'Custom Trap',
                TrapType::CUSTOM,
                TrapStatus::ARMED,
                true
            ),
        ]);
    }
}
