<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Actions;

use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\Contracts\Models\Game;
use Smorken\Domain\Actions\Contracts\Action;

interface SetGameStatusAction extends Action
{
    public function __invoke(Game $game, Status $status): Game;
}
