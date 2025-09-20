<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Services;

use Domain\Board\GameBoard\DataObjects\Board as BoardData;
use Domain\Board\GameSession\Contracts\Actions\NewGameAction;
use Domain\Board\GameSession\Contracts\Generators\JoinKeyGenerator;
use Domain\Board\GameSession\Contracts\Repositories\JoinKeyExistsRepository;
use Domain\Board\GameSession\DataObjects\Game;
use Domain\Board\GameSession\DataObjects\Game as GameData;

class StartNewGameService implements \Domain\Board\GameSession\Contracts\Services\StartNewGameService
{
    public function __construct(
        protected NewGameAction $newGameAction,
        protected JoinKeyExistsRepository $joinKeyExistsRepository,
        protected JoinKeyGenerator $joinKeyGenerator,
    ) {}

    public function __invoke(BoardData $board): GameData
    {
        $key = retry(10, fn () => $this->generateKey());
        $game = ($this->newGameAction)($board, $key);

        return Game::fromGameModel($game);
    }

    protected function generateKey(): string
    {
        $key = ($this->joinKeyGenerator)();
        if (($this->joinKeyExistsRepository)($key)) {
            throw new \OutOfBoundsException("$key already exists");
        }

        return $key;
    }
}
