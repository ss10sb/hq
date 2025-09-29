<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\Contracts\Services\JoinGameService;
use Domain\Board\GameSession\Events\GameHeroesUpdated;
use Domain\Board\GameSession\Events\PlayerJoinedGame;
use Domain\Board\GameSession\Events\PlayerJoinedWaitingRoom;
use Domain\Board\GameSession\Exceptions\GameException;
use Domain\Shared\Contracts\Models\User;
use Domain\Shared\DataObjects\Player;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class JoinGameController
{
    public function __construct(
        protected JoinGameService $joinGameService,
        #[CurrentUser] protected User $user
    ) {}

    public function __invoke(int $heroId, string $joinKey): RedirectResponse
    {
        try {
            $game = ($this->joinGameService)($joinKey, $heroId);
        } catch (GameException $e) {
            return Redirect::route('hero.select')->withErrors(['error' => $e->getMessage()]);
        }

        // Notify other players in the waiting room that this user has joined
        $userId = (int) $this->user->getAuthIdentifier();
        $player = new Player(id: $userId, name: $this->user->name);

        if ($game->status === Status::PENDING) {
            PlayerJoinedWaitingRoom::dispatch($game->id, $player);

            return Redirect::route('game.waiting-room', ['id' => $game->id]);
        }

        PlayerJoinedGame::dispatch($game->id, $player);
        GameHeroesUpdated::dispatch($game->id, $game->heroes->toArray());

        return Redirect::route('game.play', ['id' => $game->id]);
    }
}
