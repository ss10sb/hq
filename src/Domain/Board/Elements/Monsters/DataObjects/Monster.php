<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Monsters\DataObjects;

use Domain\Board\Elements\DataObjects\Stats;
use Domain\Board\Elements\Monsters\Constants\MonsterType;
use Smorken\Domain\DataObjects\DataObject;

/**
 * Only used to provide monster details to the frontend
 */
final class Monster extends DataObject
{
    public function __construct(
        public string $name,
        public MonsterType $type,
        public Stats $stats,
        public int $movementSquares,
    ) {}
}
