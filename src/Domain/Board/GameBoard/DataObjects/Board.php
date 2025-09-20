<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\DataObjects;

use Domain\Board\Elements\DataObjects\Element;
use Domain\Board\Elements\DataObjects\Elements;
use Domain\Board\GameBoard\Constants\BoardGroup;
use Domain\Board\GameBoard\Constants\Dimensions;
use Domain\Board\GameBoard\Contracts\Models\Board as BoardModel;
use Domain\Board\GameSession\Contracts\Models\Game;
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
        public Tiles $tiles,
        public Elements $elements,
    ) {}

    public static function fromBoardModel(BoardModel $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            group: $model->group,
            order: $model->order,
            width: $model->width,
            height: $model->height,
            isPublic: $model->is_public,
            tiles: $model->tiles,
            elements: $model->elements,
        );
    }

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
            elements: new Elements,
        );
    }

    public static function fromGameModel(Game $model): self
    {
        $board = $model->board;

        return new self(
            id: $board->id,
            name: $board->name,
            group: $board->group,
            order: $board->order,
            width: $board->width,
            height: $board->height,
            isPublic: $board->is_public,
            tiles: $model->tiles,
            elements: $model->elements,
        );
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            id: $request->input('id') ?? 0,
            name: $request->input('name'),
            group: BoardGroup::from($request->input('group')),
            order: (int) $request->input('order'),
            width: (int) $request->input('width'),
            height: (int) $request->input('height'),
            isPublic: (bool) $request->input('is_public'),
            tiles: Tiles::fromRequest($request),
            elements: Elements::fromRequest($request),
        );
    }

    public function addElement(Element $element): self
    {
        $this->elements->addElement($element);

        return $this;
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
            'group' => $this->group,
            'order' => $this->order,
            'width' => $this->width,
            'height' => $this->height,
            'is_public' => $this->isPublic,
            'tiles' => $this->tiles,
            'elements' => $this->elements,
        ];
    }
}
