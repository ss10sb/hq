<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\DataObjects;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Smorken\Domain\DataObjects\DataObject;

/**
 * @method bool isEmpty()
 * @method bool isNotEmpty()
 */
class Tiles extends DataObject
{
    public Collection $tiles;

    public function __construct(iterable $tiles = [])
    {
        $this->tiles = new Collection;
        $this->ensureTiles($tiles);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }

    public static function fromRequest(Request $request): self
    {
        return self::fromArray($request->input('tiles') ?? []);
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->tiles, $name], $arguments);
    }

    public function addTile(Tile $tile): self
    {
        if (! $this->tiles->has($tile->y)) {
            $this->tiles->put($tile->y, new Collection);
        }
        $row = $this->getRow($tile->y);
        $row->put($tile->x, $tile);

        return $this;
    }

    public function getRow(int $row): Collection
    {
        if (! $this->tiles->has($row)) {
            $this->tiles->put($row, new Collection);
        }

        return $this->tiles->get($row);
    }

    public function toArray(): array
    {
        return $this->tiles->toArray();
    }

    protected function ensureTiles(iterable $tiles): void
    {
        foreach ($tiles as $row) {
            if ($row instanceof Tile) {
                $this->addTile($row);

                continue;
            }
            foreach ($row as $tile) {
                if (! $tile instanceof Tile) {
                    $tile = Tile::from($tile);
                }
                $this->addTile($tile);
            }
        }
    }
}
