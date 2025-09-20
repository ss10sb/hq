<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Constants;

enum HeroArchetype: string
{
    case BERSERKER = 'berserker';
    case WIZARD = 'wizard';
    case ELF = 'elf';
    case DWARF = 'dwarf';
    case CUSTOM = 'custom';
}
