<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Constants;

enum TrapType: string
{
    case PIT = 'pit';
    case SPEAR = 'spear';
    case BLOCK = 'block';
    case CUSTOM = 'custom';
}
