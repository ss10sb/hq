<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\DataObjects;

use Illuminate\Support\Collection;
use Smorken\Domain\DataObjects\DataObject;

final class Equipment extends DataObject
{
    public Collection $equipmentItems;

    public function __construct(
        iterable $equipmentItems = [],
    ) {
        $this->equipmentItems = new Collection;
        $this->addEquipmentItems($equipmentItems);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }

    public function addItem(EquipmentItem $equipmentItem): self
    {
        $this->equipmentItems->push($equipmentItem);

        return $this;
    }

    public function toArray(): array
    {
        return $this->equipmentItems->toArray();
    }

    protected function addEquipmentItems(iterable $equipmentItems): void
    {
        foreach ($equipmentItems as $equipmentItem) {
            if (is_a($equipmentItem, EquipmentItem::class)) {
                $this->addItem($equipmentItem);

                continue;
            }
            $this->addItem(EquipmentItem::from($equipmentItem));
        }
    }
}
