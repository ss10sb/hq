<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Services;

use Domain\Board\GameBoard\DataObjects\Board as BoardData;
use Domain\Board\GameSession\DataObjects\Game as GameData;

interface StartNewGameService
{
    public function __invoke(BoardData $board): GameData;
}
