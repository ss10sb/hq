<?php

declare(strict_types=1);

namespace App\Http\Controllers\Board;

use Domain\Board\Elements\Factories\DefaultsRepositoryFactory;
use Domain\Board\GameBoard\Constants\GameExpansion;
use Domain\Board\GameBoard\Contracts\Actions\SaveBoardAction;
use Domain\Board\GameBoard\Contracts\Repositories\FindBoardRepository;
use Domain\Board\GameBoard\DataObjects\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class EditBoardController
{
    public function __construct(
        protected FindBoardRepository $findBoardRepository,
        protected SaveBoardAction $saveBoardAction,
        protected DefaultsRepositoryFactory $defaultsRepositoryFactory,
    ) {}

    public function __invoke(Request $request, int $id): Response
    {
        $board = ($this->findBoardRepository)($id);
        $boardData = Board::fromBoardModel($board);

        return Inertia::render('board/Edit', [
            'board' => $boardData,
            'canEdit' => $board->creator_id === (int) $request->user()->getAuthIdentifier(),
            'groups' => GameExpansion::toSelectList(),
            'monsters' => $this->defaultsRepositoryFactory->monsters(),
            'traps' => $this->defaultsRepositoryFactory->traps(),
            'fixtures' => $this->defaultsRepositoryFactory->fixtures(),
        ]);
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        $boardData = Board::fromRequest($request);
        ($this->saveBoardAction)($id, $boardData);

        return Redirect::back()->withErrors($this->saveBoardAction->getMessages());
    }
}
