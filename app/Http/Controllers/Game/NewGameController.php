<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Domain\Board\GameBoard\Contracts\Repositories\FindBoardRepository;
use Domain\Board\GameBoard\DataObjects\Board;
use Domain\Board\GameSession\Contracts\Services\StartNewGameService;
use Domain\Board\GameSession\Events\PlayerJoinedWaitingRoom;
use Domain\Shared\Contracts\Models\User;
use Domain\Shared\DataObjects\Player;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class NewGameController
{
    public function __construct(
        protected FindBoardRepository $findBoardRepository,
        protected StartNewGameService $startNewGameService,
        #[CurrentUser] protected User $user
    ) {}

    public function __invoke(int $boardId): RedirectResponse
    {
        $boardModel = ($this->findBoardRepository)($boardId);
        $game = ($this->startNewGameService)(Board::fromModel($boardModel));

        $player = new Player((int) $this->user->getAuthIdentifier(), $this->user->name);
        PlayerJoinedWaitingRoom::dispatch($game->id, $player);

        return Redirect::route('game.waiting-room', ['id' => $game->id]);
    }
}
