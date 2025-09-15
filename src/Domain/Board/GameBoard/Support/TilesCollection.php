<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Support;

use Domain\Board\GameBoard\DataObjects\Tile;
use Illuminate\Support\Collection;

class TilesCollection extends Collection implements \Domain\Board\GameBoard\Contracts\Support\TilesCollection
{
    public function __construct($items = [])
    {
        $this->items = $this->ensureTiles($items);
    }

    public function addTile(Tile $tile): self
    {
        /** @var ?Collection $row */
        $row = $this->get($tile->y);
        if (! $row) {
            $this->items[$tile->y] = new Collection;
        }
        $row->put($tile->x, $tile);

        return $this;
    }

    public function getRow(int $row): Collection
    {
        return $this->get($row, new Collection);
    }

    public function getTile(int $x, int $y): ?Tile
    {
        return $this->getRow($y)->get($x);
    }

    protected function ensureTiles(array $items): array
    {
        $tiles = [];
        foreach ($items as $y => $row) {
            $tiles[$y] = $this->newRow($row);
        }

        return $tiles;
    }

    protected function newRow(iterable $row): Collection
    {
        $collection = new Collection;
        foreach ($row as $x => $tile) {
            if (! $tile instanceof Tile) {
                $tile = Tile::fromArray($tile);
            }
            $collection->put($x, $tile);
        }

        return $collection;
    }
}
