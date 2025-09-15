<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Support;

use Domain\Board\GameBoard\DataObjects\Tile;

class TilesGenerator
{
    public static function generate(int $rows, int $columns): array
    {
        $tiles = [];
        for ($y = 0; $y < $rows; $y++) {
            $row = [];
            for ($x = 0; $x < $columns; $x++) {
                $row[$x] = Tile::newWall($x, $y);
            }
            $tiles[$y] = $row;
        }

        return $tiles;
    }
}
