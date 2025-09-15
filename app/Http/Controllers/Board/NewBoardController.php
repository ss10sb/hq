<?php

declare(strict_types=1);

namespace App\Http\Controllers\Board;

use Domain\Board\GameBoard\Constants\BoardGroup;
use Domain\Board\GameBoard\Contracts\Actions\NewBoardAction;
use Domain\Board\GameBoard\DataObjects\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewBoardController
{
    public function __construct(
        protected NewBoardAction $newBoardAction,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('board/New', [
            'board' => Board::fromDefaults(),
            'groups' => BoardGroup::toSelectList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $board = ($this->newBoardAction)(Board::fromRequest($request));
        if ($this->newBoardAction->getMessages()->isNotEmpty()) {
            return redirect()->back()->withErrors($this->newBoardAction->getMessages());
        }

        return redirect()->route('board.edit', ['id' => $board->id]);
    }
}
