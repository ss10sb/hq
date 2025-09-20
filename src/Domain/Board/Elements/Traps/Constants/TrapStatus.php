<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Traps\Constants;

enum TrapStatus: string
{
    case ARMED = 'armed';
    case DISARMED = 'disarmed';
    case TRIGGERED = 'triggered';
}
