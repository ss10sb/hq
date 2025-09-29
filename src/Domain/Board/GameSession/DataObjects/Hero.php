<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\DataObjects;

use Domain\Board\Elements\DataObjects\Stats;
use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Domain\Board\Elements\Heros\Contracts\Models\Hero as HeroModel;
use Domain\Board\Elements\Heros\DataObjects\Equipment;
use Domain\Board\Elements\Heros\DataObjects\Inventory;
use Domain\Board\GameSession\Contracts\Models\GameHero;

final class Hero extends Character
{
    public function __construct(
        int $id,
        string $name,
        int $playerId,
        public readonly HeroArchetype $type,
        public readonly Stats $stats,
        public readonly Inventory $inventory,
        public readonly Equipment $equipment,
        public readonly int $gold,
        public readonly int $x,
        public readonly int $y,

    ) {
        parent::__construct($id, $name, $playerId);
    }

    public static function from(array|string $data): self
    {
        if (is_string($data)) {
            return self::fromJson($data);
        }

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? 'Unknown',
            playerId: $data['playerId'] ?? 0,
            type: isset($data['type']) ? HeroArchetype::from($data['type']) : HeroArchetype::CUSTOM,
            stats: Stats::fromArray($data['stats'] ?? []),
            inventory: Inventory::fromArray($data['inventory'] ?? []),
            equipment: Equipment::fromArray($data['equipment'] ?? []),
            gold: $data['gold'] ?? 0,
            x: $data['x'] ?? 0,
            y: $data['y'] ?? 0,
        );
    }

    public static function fromGameHeroModel(GameHero $gameHero): self
    {
        return self::fromHeroModel(
            $gameHero->hero,
            $gameHero->user_id,
            $gameHero->x,
            $gameHero->y,
            $gameHero->body_points
        );
    }

    public static function fromHeroModel(HeroModel $hero, int $playerId, int $x, int $y, int $bodyPoints): self
    {
        $stats = $hero->stats->updateCurrentBodyPoints($bodyPoints);

        return new self(
            id: $hero->id,
            name: $hero->name,
            playerId: $playerId,
            type: $hero->type,
            stats: $stats,
            inventory: $hero->inventory,
            equipment: $hero->equipment,
            gold: $hero->gold ?? 0,
            x: $x,
            y: $y,
        );
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }

    public static function isHero(array|string $data): bool
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (isset($data['type'])) {
            return true;
        }

        return false;
    }
}
