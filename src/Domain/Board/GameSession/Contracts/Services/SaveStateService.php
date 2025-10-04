<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Services;

use Domain\Board\GameSession\Contracts\Models\Game;
use Domain\Board\GameSession\DataObjects\SaveState;

interface SaveStateService
{
    public function __invoke(SaveState $state, bool $updateHeroes = false): Game;
}
