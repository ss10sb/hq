<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Contracts\Actions;

use Domain\Board\GameBoard\Contracts\Models\Board;
use Domain\Board\GameBoard\DataObjects\Board as BoardData;
use Smorken\Domain\Actions\Contracts\Action;

interface NewBoardAction extends Action
{
    public function __invoke(BoardData $newBoard): Board;
}
