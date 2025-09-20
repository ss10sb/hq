<?php

declare(strict_types=1);

namespace Domain\Shared\DataObjects;

use Domain\Shared\Contracts\Models\User;
use Smorken\Domain\DataObjects\DataObject;

final class Player extends DataObject
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}

    public static function from(User|string|array $data): self
    {
        if ($data instanceof User) {
            return new self(
                id: $data->id,
                name: $data->name,
            );
        }
        if (is_string($data)) {
            return self::fromJson($data);
        }

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
        );
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }
}
