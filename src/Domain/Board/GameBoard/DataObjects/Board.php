<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\DataObjects;

use Domain\Board\GameBoard\Constants\BoardGroup;
use Domain\Board\GameBoard\Constants\Dimensions;
use Illuminate\Http\Request;
use Smorken\Domain\DataObjects\DataObject;

class Board extends DataObject
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly BoardGroup $group,
        public readonly int $order,
        public readonly int $width,
        public readonly int $height,
        public readonly bool $isPublic,
        public Tiles $tiles
    ) {}

    public static function fromDefaults(): self
    {
        return new self(
            id: 0,
            name: '',
            group: BoardGroup::CORE,
            order: 1,
            width: Dimensions::WIDTH->value,
            height: Dimensions::HEIGHT->value,
            isPublic: false,
            tiles: new Tiles,
        );
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            id: 0,
            name: $request->input('name'),
            group: BoardGroup::from($request->input('group')),
            order: (int) $request->input('order'),
            width: (int) $request->input('width'),
            height: (int) $request->input('height'),
            isPublic: (bool) $request->input('is_public'),
            tiles: new Tiles,
        );
    }

    public function addTile(Tile $tile): self
    {
        $this->tiles->addTile($tile);

        return $this;
    }

    public function toModelArray(): array
    {
        return [
            'name' => $this->name,
            'group' => $this->group->value,
            'order' => $this->order,
            'width' => $this->width,
            'height' => $this->height,
            'is_public' => $this->isPublic,
            'tiles' => $this->tiles->toArray(),
        ];
    }
}
