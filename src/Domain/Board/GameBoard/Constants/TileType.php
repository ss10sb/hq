<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Constants;

enum TileType: string
{
    case WALL = 'wall';
    case FLOOR = 'floor';
    case FIXTURE = 'fixture';
}
