<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\DataObjects;

final class Zargon extends Character
{
    public function __construct(
        int $id = 0,
        string $name = 'Zargon',
        int $playerId = 0,
    ) {
        parent::__construct($id, $name, $playerId);
    }

    public static function from(array|string $data): self
    {
        return new self(playerId: $data['playerId'] ?? 0);
    }
}
