<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Fixtures\DataObjects;

use Domain\Board\GameBoard\Constants\FixtureType;
use Domain\Board\GameBoard\Constants\GameExpansion;
use Smorken\Domain\DataObjects\DataObject;

final class Fixture extends DataObject
{
    public function __construct(
        public readonly string $name,
        public readonly FixtureType $type,
        public readonly bool $traversable = false,
        public readonly GameExpansion $gameExpansion = GameExpansion::CORE
    ) {}
}
