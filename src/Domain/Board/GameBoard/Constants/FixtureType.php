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
    case STAIRWAY = 'stairway'; // kk
    case CLIFF = 'cliff'; // kk
    case TRAPDOOR = 'trapdoor'; // kk
    case WEAPONS_FORGE = 'weapons-forge'; // kk
    case CLOUD_OF_DREAD = 'cloud-of-dread'; // kk
    case STATUE = 'statue'; // kk
    case CUSTOM = 'custom';
}
