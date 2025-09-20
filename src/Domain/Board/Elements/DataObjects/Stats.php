<?php

declare(strict_types=1);

namespace Domain\Board\Elements\DataObjects;

use Smorken\Domain\DataObjects\DataObject;

final class Stats extends DataObject
{
    public function __construct(
        public readonly int $bodyPoints,
        public readonly int $mindPoints,
        public readonly int $attackDice,
        public readonly int $defenseDice,
        public readonly int $currentBodyPoints,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            bodyPoints: $data['bodyPoints'] ?? 1,
            mindPoints: $data['mindPoints'] ?? 1,
            attackDice: $data['attackDice'] ?? 1,
            defenseDice: $data['defenseDice'] ?? 1,
            currentBodyPoints: $data['currentBodyPoints'] ?? 1,
        );
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }
}
