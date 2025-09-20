<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\DataObjects;

use Illuminate\Support\Collection;
use Smorken\Domain\DataObjects\DataObject;

final class Inventory extends DataObject
{
    public Collection $items;

    public function __construct(
        iterable $items = [],
    ) {
        $this->items = new Collection;
        $this->addInventoryItems($items);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }

    public function addItem(InventoryItem $item): self
    {
        $this->items->push($item);

        return $this;
    }

    public function toArray(): array
    {
        return $this->items->toArray();
    }

    protected function addInventoryItems(iterable $items): void
    {
        foreach ($items as $item) {
            if (is_a($item, InventoryItem::class)) {
                $this->addItem($item);

                continue;
            }
            $this->addItem(InventoryItem::from($item));
        }
    }
}
