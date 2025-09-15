<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Constants;

enum FixtureType: string
{
    case TREASURE_CHEST = 'treasure-chest';
    case CHAIR = 'chair';
    case TABLE = 'table';
    case BOOKCASE = 'bookcase';
    case CUPBOARD = 'cupboard';
    case ALCHEMIST_BENCH = 'alchemist-bench';
    case ALTAR = 'altar';
    case THRONE = 'throne';
    case FIREPLACE = 'fireplace';
    case TORTURE_RACK = 'torture-rack';
    case TOMB = 'tomb';
    case SORCERERS_TABLE = 'sorcerers-table';
    case CUSTOM = 'custom';
}
