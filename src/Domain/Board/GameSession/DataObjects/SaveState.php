<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\DataObjects;

use Domain\Board\Elements\DataObjects\Elements;
use Domain\Board\GameBoard\DataObjects\Tiles;
use Illuminate\Http\Request;
use Smorken\Domain\DataObjects\DataObject;

final class SaveState extends DataObject
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $currentHeroId,
        public readonly Elements $elements,
        public readonly Tiles $tiles,
        public readonly Heroes $heroes,
    ) {}

    public static function fromRequest(Request $request, int $id): self
    {
        return new self(
            id: $id,
            currentHeroId: $request->input('currentHeroId') ?? null,
            elements: Elements::fromArray($request->input('elements') ?? []),
            tiles: Tiles::fromArray($request->input('tiles') ?? []),
            heroes: Heroes::fromArray($request->input('heroes') ?? []),
        );
    }

    public function toModelArray(): array
    {
        return array_filter([
            'current_hero_id' => $this->currentHeroId,
            'elements' => $this->elements,
            'tiles' => $this->tiles,
            'heroes' => $this->heroes,
        ]);
    }
}
