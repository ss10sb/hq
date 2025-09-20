<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\DataObjects;

use Smorken\Domain\DataObjects\DataObject;

abstract class Character extends DataObject
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $playerId,
    ) {}
}
