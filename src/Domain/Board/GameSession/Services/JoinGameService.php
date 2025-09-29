<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Services;

use Domain\Board\Elements\Constants\ElementType;
use Domain\Board\Elements\DataObjects\Elements;
use Domain\Board\Elements\Heros\Contracts\Models\Hero as HeroModel;
use Domain\Board\Elements\Heros\Contracts\Repositories\FindHeroRepository;
use Domain\Board\GameSession\Contracts\Models\Game as GameModel;
use Domain\Board\GameSession\Contracts\Repositories\FindAvailableGameRepository;
use Domain\Board\GameSession\DataObjects\Game;
use Domain\Board\GameSession\DataObjects\Hero;
use Domain\Board\GameSession\Events\GameHeroesUpdated;
use Domain\Board\GameSession\Exceptions\GameNotFoundException;
use Domain\Board\GameSession\Exceptions\HeroNotAllowedException;
use Domain\Shared\Contracts\Models\User;
use Illuminate\Container\Attributes\CurrentUser;

class JoinGameService implements \Domain\Board\GameSession\Contracts\Services\JoinGameService
{
    public function __construct(
        protected FindAvailableGameRepository $findAvailableGameRepository,
        protected FindHeroRepository $findHeroRepository,
        #[CurrentUser] protected User $user
    ) {}

    public function __invoke(string $joinKey, int $heroId): Game
    {
        $game = ($this->findAvailableGameRepository)($joinKey);
        $this->checkGame($game);
        $hero = ($this->findHeroRepository)($heroId);
        $this->checkHero($hero);
        $this->addHeroToGame($game, $hero);
        $this->addPlayerToGame($game);
        $game->save();
        $game->refresh();

        $gameData = Game::fromGameModel($game);

        return $gameData;
    }

    protected function addGameHero(GameModel $game, Hero $hero): void
    {
        /** @var ?\Domain\Board\GameSession\Contracts\Models\GameHero $gameHero */
        $gameHero = $game->gameHeroes->first(function ($gameHero) use ($hero) {
            return $gameHero->hero_id === $hero->id;
        });
        if ($gameHero) {
            $gameHero->update([
                'user_id' => $hero->playerId,
                'body_points' => $hero->stats->bodyPoints,
                'x' => $hero->x,
                'y' => $hero->y,
            ]);

            return;
        }
        $game->gameHeroes()->create([
            'hero_id' => $hero->id,
            'user_id' => $hero->playerId,
            'body_points' => $hero->stats->bodyPoints,
            'order' => $this->getOrder($game),
            'x' => $hero->x,
            'y' => $hero->y,
        ]);
    }

    protected function addHeroToGame(GameModel $game, HeroModel $hero): void
    {
        if ($this->gameHasHero($game, $hero->id)) {
            return;
        }
        $hero = $this->createHeroDataObject($hero, $game);
        $this->addGameHero($game, $hero);
    }

    protected function addPlayerToGame(GameModel $game): void
    {
        if ($this->isUserGameMaster($game)) {
            return;
        }
        if ($this->gameHasUser($game)) {
            return;
        }
        $game->users()->attach((int) $this->user->getAuthIdentifier());
    }

    protected function checkGame(?GameModel $game): void
    {
        if (! $game) {
            throw GameNotFoundException::make();
        }
    }

    protected function checkHero(HeroModel $hero): void
    {
        if (! $hero->userIsAllowed((int) $this->user->getAuthIdentifier())) {
            throw HeroNotAllowedException::make();
        }
    }

    protected function createHeroDataObject(HeroModel $hero, GameModel $game): Hero
    {
        ['x' => $x, 'y' => $y] = $this->findFirstAvailableStartPosition($game);

        return Hero::fromHeroModel($hero, (int) $this->user->getAuthIdentifier(), $x, $y, $hero->stats->bodyPoints);
    }

    /**
     * @return array{x:int,y:int}
     */
    protected function findFirstAvailableStartPosition(GameModel $game): array
    {
        $elements = $game->elements ?? new Elements;

        // Find first available PlayerStart spot
        foreach ($elements->elements as $idx => $el) {
            if ($el->type === ElementType::PLAYER_START) {
                $elements->elements->forget($idx);

                return ['x' => $el->x, 'y' => $el->y];
            }
        }

        return ['x' => 0, 'y' => 0];
    }

    protected function gameHasHero(GameModel $game, int $heroId): bool
    {
        foreach ($game->heroes as $gameHero) {
            if ($gameHero->id === $heroId) {
                return true;
            }
        }

        return false;
    }

    protected function gameHasUser(GameModel $game): bool
    {
        $userId = (int) $this->user->getAuthIdentifier();
        foreach ($game->users as $gamePlayer) {
            if ($gamePlayer->id === $userId) {
                return true;
            }
        }

        return false;
    }

    protected function getOrder(GameModel $game): int
    {
        return ($game->gameHeroes->max('order') + 1) ?? 0;
    }

    protected function isUserGameMaster(GameModel $game): bool
    {
        return $game->game_master_id === (int) $this->user->getAuthIdentifier();
    }
}
