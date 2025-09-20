<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Actions;

use Domain\Board\GameBoard\DataObjects\Board;
use Domain\Board\GameSession\Contracts\Models\Game;
use Smorken\Domain\Actions\Contracts\Action;

interface NewGameAction extends Action
{
    public function __invoke(Board $board, string $joinKey): Game;
}
