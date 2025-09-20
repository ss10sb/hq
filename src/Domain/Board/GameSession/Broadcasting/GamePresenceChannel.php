<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Broadcasting;

use Domain\Board\GameSession\Contracts\Repositories\FindGameRepository;
use Domain\Board\GameSession\DataObjects\Game;
use Domain\Shared\Contracts\Models\User;

class GamePresenceChannel
{
    public function __construct(
        protected FindGameRepository $findGameRepository,
    ) {}

    public function join(User $user, int $gameId): array|false
    {
        $gameModel = ($this->findGameRepository)($gameId);
        $game = Game::fromGameModel($gameModel);

        return $game->userIsAllowed((int) $user->id) ? [
            'id' => (int) $user->id,
            'name' => (string) ($user->name ?? 'User #'.$user->id),
        ] : false;
    }
}
