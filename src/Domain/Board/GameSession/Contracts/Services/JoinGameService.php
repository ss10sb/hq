<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Services;

use Domain\Board\GameSession\DataObjects\Game;

interface JoinGameService
{
    public function __invoke(string $joinKey, int $heroId): Game;
}
