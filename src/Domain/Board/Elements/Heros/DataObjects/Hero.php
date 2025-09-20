<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\DataObjects;

use Domain\Board\Elements\DataObjects\Stats;
use Domain\Board\Elements\Heros\Constants\HeroArchetype;
use Domain\Board\Elements\Heros\Contracts\Models\Hero as HeroModel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Smorken\Domain\DataObjects\DataObject;

final class Hero extends DataObject
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly HeroArchetype $type,
        public readonly Stats $stats,
        public readonly Inventory $inventory,
        public readonly Equipment $equipment,
    ) {}

    public static function from(Hero|array|string $data): self
    {
        if ($data instanceof Hero) {
            return $data;
        }
        if (is_string($data)) {
            return self::fromJson($data);
        }

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? 'Unknown',
            type: isset($data['type']) ? HeroArchetype::from($data['type']) : HeroArchetype::CUSTOM,
            stats: Stats::fromArray($data['stats'] ?? []),
            inventory: Inventory::fromArray($data['inventory'] ?? []),
            equipment: Equipment::fromArray($data['equipment'] ?? []),
        );
    }

    public static function fromHeroModel(HeroModel $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            type: $model->type,
            stats: $model->stats,
            inventory: $model->inventory,
            equipment: $model->equipment,
        );
    }

    public static function fromIterable(iterable $items): Collection
    {
        $heroes = new Collection;
        foreach ($items as $hero) {
            $heroes->push(self::from($hero));
        }

        return $heroes;
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }

    public static function fromRequest(Request $request, ?int $id = null): self
    {
        return new self(
            id: $id ?? 0,
            name: $request->input('name'),
            type: HeroArchetype::from($request->input('type')),
            stats: Stats::fromArray($request->input('stats')),
            inventory: Inventory::fromArray($request->input('inventory')),
            equipment: Equipment::fromArray($request->input('equipment')),
        );
    }

    public function toModelArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'stats' => $this->stats,
            'inventory' => $this->inventory,
            'equipment' => $this->equipment,
        ];
    }
}
