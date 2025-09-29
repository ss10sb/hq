<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\DataObjects;

use Domain\Board\GameBoard\Constants\TileType;
use Smorken\Domain\DataObjects\DataObject;

class Tile extends DataObject
{
    public function __construct(
        public readonly string $id,
        public readonly int $x,
        public readonly int $y,
        public readonly TileType $type,
        public readonly bool $visible = false,
        public readonly ?string $name = null,
    ) {}

    public static function from(array|string $data): self
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            x: $data['x'],
            y: $data['y'],
            type: TileType::from($data['type']),
            visible: (bool) ($data['visible'] ?? false),
            name: $data['name'] ?? null,
        );
    }

    public static function newWall(int $x, int $y): self
    {
        return new self(
            id: "{$x}:{$y}",
            x: $x,
            y: $y,
            type: TileType::WALL,
            visible: false,
        );
    }
}
