<?php

declare(strict_types=1);

namespace Domain\Board\Elements\DataObjects;

use Smorken\Domain\DataObjects\DataObject;

final class Properties extends DataObject
{
    public function __construct(
        public readonly bool $interactive,
        public readonly bool $hidden,
        public readonly bool $traversable,
    ) {}

    public static function asDefaults(): self
    {
        return new self(true, true, false);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            interactive: $data['interactive'] ?? true,
            hidden: $data['hidden'] ?? true,
            traversable: $data['traversable'] ?? false,
        );
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }
}
