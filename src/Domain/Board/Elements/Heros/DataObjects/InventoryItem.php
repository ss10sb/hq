<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\DataObjects;

use Smorken\Domain\DataObjects\DataObject;

final class InventoryItem extends DataObject
{
    public function __construct(
        public readonly string $name,
        public readonly int $quantity,
        public readonly string $description = '',
    ) {}

    public static function from(string|array $data): self
    {
        if (is_string($data)) {
            return self::fromJson($data);
        }

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            quantity: $data['quantity'],
            description: $data['description'] ?? '',
        );
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }
}
