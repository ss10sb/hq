<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Traps\DataObjects;

use Domain\Board\Elements\Traps\Constants\TrapStatus;
use Domain\Board\Elements\Traps\Constants\TrapType;
use Domain\Board\GameBoard\Constants\GameExpansion;
use Smorken\Domain\DataObjects\DataObject;

/**
 * Only used to provide trap details to the frontend
 */
final class Trap extends DataObject
{
    public function __construct(
        public string $name,
        public TrapType $type,
        public TrapStatus $status,
        public bool $traversable,
        public GameExpansion $gameExpansion = GameExpansion::CORE
    ) {}
}
