<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Domain\Board\GameBoard\DataObjects\Board;
use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\Contracts\Repositories\FindGameRepository;
use Domain\Board\GameSession\DataObjects\Game;
use Domain\Board\GameSession\Events\PlayerJoinedWaitingRoom;
use Domain\Shared\Contracts\Models\User;
use Domain\Shared\DataObjects\Player;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class WaitingRoomController
{
    public function __construct(
        protected FindGameRepository $findGameRepository,
        #[CurrentUser] protected User $user,
    ) {}

    public function __invoke(int $id): Response|RedirectResponse
    {
        $gameModel = ($this->findGameRepository)($id);
        $game = Game::fromGameModel($gameModel);
        if ($response = $this->validateGame($game)) {
            return $response;
        }

        // Broadcast presence event that this user has joined/entered the waiting room
        $userId = (int) $this->user->getAuthIdentifier();
        $player = new Player(id: $userId, name: $this->user->name);
        PlayerJoinedWaitingRoom::dispatch($game->id, $player);

        $board = Board::fromBoardModel($gameModel->board);

        return Inertia::render('game/WaitingRoom', [
            'game' => $game,
            'board' => $board,
        ]);
    }

    protected function validateGame(Game $game): ?RedirectResponse
    {
        if ($game->status === Status::COMPLETED || $game->status === Status::ABORTED) {
            return Redirect::route('dashboard')->with('error', 'Game has already ended.');
        }
        $userId = (int) $this->user->getAuthIdentifier();
        if ($game->userIsAllowed($userId)) {
            return null;
        }

        return Redirect::route('dashboard')->with('error', 'You are not a player in this game.');
    }
}
