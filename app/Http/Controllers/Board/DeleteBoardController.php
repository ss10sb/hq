<?php

declare(strict_types=1);

namespace App\Http\Controllers\Board;

use Domain\Board\GameBoard\Contracts\Actions\DeleteBoardAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class DeleteBoardController
{
    public function __construct(
        protected DeleteBoardAction $deleteBoardAction,
    ) {}

    public function __invoke(int $id): RedirectResponse
    {
        ($this->deleteBoardAction)($id);

        return Redirect::route('board.select')->withErrors($this->deleteBoardAction->getMessages());
    }
}
