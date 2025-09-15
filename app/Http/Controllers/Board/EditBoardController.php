<?php

declare(strict_types=1);

namespace App\Http\Controllers\Board;

use Domain\Board\GameBoard\Constants\BoardGroup;
use Domain\Board\GameBoard\Contracts\Repositories\FindBoardRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class EditBoardController
{
    public function __construct(
        protected FindBoardRepository $findBoardRepository,
    ) {}

    public function __invoke(Request $request, int $id): Response
    {
        $board = ($this->findBoardRepository)($id);

        return Inertia::render('board/Edit', [
            'board' => $board->only(['id', 'name', 'group', 'order', 'width', 'height', 'tiles']),
            'canEdit' => $board->creator_id === (int) $request->user()->getAuthIdentifier(),
            'groups' => BoardGroup::toSelectList(),
        ]);
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        dd($request->all());
        $board = ($this->findBoardRepository)($id);

        // TODO store board state
        return Redirect::back();
    }
}
