<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Monsters\Constants;

enum MonsterType: string
{
    case GOBLIN = 'goblin';
    case SKELETON = 'skeleton';
    case ZOMBIE = 'zombie';
    case MUMMY = 'mummy';
    case GARGOYLE = 'gargoyle';
    case DREAD_WARRIOR = 'dread-warrior';
    case ABOMINATION = 'abomination';
    case ORC = 'orc';
    case CUSTOM = 'custom';
}
