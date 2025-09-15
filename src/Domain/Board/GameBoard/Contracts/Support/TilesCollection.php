<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Contracts\Support;

use Domain\Board\GameBoard\DataObjects\Tile;
use Illuminate\Support\Collection;

interface TilesCollection
{
    public function getRow(int $row): Collection;

    public function getTile(int $x, int $y): ?Tile;
}
