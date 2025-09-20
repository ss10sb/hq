<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Domain\Board\Elements\Factories\DefaultsRepositoryFactory;
use Domain\Board\GameBoard\DataObjects\Board;
use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\Contracts\Actions\SetGameStatusAction;
use Domain\Board\GameSession\Contracts\Models\Game;
use Domain\Board\GameSession\Contracts\Repositories\FindGameRepository;
use Domain\Board\GameSession\Events\GameStarted;
use Domain\Board\GameSession\Events\PlayerJoinedGame;
use Domain\Shared\Contracts\Models\User;
use Domain\Shared\DataObjects\Player;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\Response;

class PlayGameController
{
    public function __construct(
        protected FindGameRepository $findGameRepository,
        protected SetGameStatusAction $setGameStatusAction,
        protected DefaultsRepositoryFactory $defaultsRepositoryFactory,
        #[CurrentUser] protected User $user,
    ) {}

    public function __invoke(int $id): Response
    {
        $gameModel = ($this->findGameRepository)($id);
        $this->updateStatus($gameModel);
        $game = \Domain\Board\GameSession\DataObjects\Game::fromGameModel($gameModel);
        $board = Board::fromGameModel($gameModel);
        $player = new Player(id: $this->user->id, name: $this->user->name);
        PlayerJoinedGame::dispatch($game->id, $player);
        GameStarted::dispatch($game->id);

        return inertia('game/PlayGame', [
            'game' => $game,
            'board' => $board,
            'monsters' => $this->defaultsRepositoryFactory->monsters(),
            'traps' => $this->defaultsRepositoryFactory->traps(),
            'fixtures' => $this->defaultsRepositoryFactory->fixtures(),
        ]);
    }

    protected function isGameMaster(Game $game): bool
    {
        return $game->game_master_id === (int) $this->user->getAuthIdentifier();
    }

    protected function updateStatus(Game $game): void
    {
        if (! $this->isGameMaster($game)) {
            return;
        }

        ($this->setGameStatusAction)(
            $game,
            Status::IN_PROGRESS,
        );
        GameStarted::dispatch($game->id);
    }
}
