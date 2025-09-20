<?php

declare(strict_types=1);

namespace Domain\Board\Elements\DataObjects;

use Domain\Board\Elements\Constants\ElementType;
use Domain\Board\Elements\Traps\Constants\TrapStatus;
use Domain\Board\Elements\Traps\Constants\TrapType;
use Smorken\Domain\DataObjects\DataObject;

final class Element extends DataObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ElementType $type,
        public readonly int $x,
        public readonly int $y,
        public readonly Properties $properties,
        public readonly ?Stats $stats,
        public readonly ?TrapType $trapType = null,
        public readonly ?TrapStatus $trapStatus = null,
    ) {}

    public static function from(array|string $data): self
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        $properties = isset($data['properties']) ? Properties::fromArray($data['properties']) : Properties::fromArray($data);
        $stats = isset($data['stats']) ? Stats::fromArray($data['stats']) : null;
        $trapType = isset($data['trapType']) && $data['trapType'] !== null ? TrapType::from($data['trapType']) : null;
        $trapStatus = isset($data['trapStatus']) && $data['trapStatus'] !== null ? TrapStatus::from($data['trapStatus']) : null;

        return new self(
            id: $data['id'],
            name: $data['name'],
            type: ElementType::from($data['type']),
            x: $data['x'],
            y: $data['y'],
            properties: $properties,
            stats: $stats,
            trapType: $trapType,
            trapStatus: $trapStatus,
        );
    }
}
