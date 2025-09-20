<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Constants;

enum FixtureType: string
{
    case ALCHEMIST_BENCH = 'alchemist-bench';
    case ALTAR = 'altar';
    case BOOKCASE = 'bookcase';
    case CHAIR = 'chair';
    case CUPBOARD = 'cupboard';
    case FIREPLACE = 'fireplace';
    case SORCERERS_TABLE = 'sorcerers-table';
    case TABLE = 'table';
    case THRONE = 'throne';
    case TOMB = 'tomb';
    case TORTURE_RACK = 'torture-rack';
    case TREASURE_CHEST = 'treasure-chest';
    case CUSTOM = 'custom';
}
