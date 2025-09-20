<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\DataObjects;

use Smorken\Domain\DataObjects\DataObject;

final class EquipmentItem extends DataObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $description = '',
        public readonly int $attackDice = 0,
        public readonly int $defenseDice = 0,
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
            description: $data['description'] ?? '',
            attackDice: $data['attackDice'] ?? 0,
            defenseDice: $data['defenseDice'] ?? 0,
        );
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }
}
