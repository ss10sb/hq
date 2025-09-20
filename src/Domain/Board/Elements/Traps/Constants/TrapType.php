<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Traps\Constants;

enum TrapType: string
{
    case PIT = 'pit';
    case SPEAR = 'spear';
    case BLOCK = 'block';
    case CUSTOM = 'custom';
}
