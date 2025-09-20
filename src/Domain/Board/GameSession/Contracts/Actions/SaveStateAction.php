<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Actions;

use Domain\Board\GameSession\Contracts\Models\Game;
use Domain\Board\GameSession\DataObjects\SaveState;
use Smorken\Domain\Actions\Contracts\Action;

interface SaveStateAction extends Action
{
    public function __invoke(SaveState $saveState): Game;
}
