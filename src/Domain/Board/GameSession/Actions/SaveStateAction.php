<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Actions;

use Domain\Board\GameSession\Contracts\Models\Game;
use Domain\Board\GameSession\Contracts\Models\GameHero;
use Domain\Board\GameSession\Contracts\Repositories\FindGameRepository;
use Domain\Board\GameSession\DataObjects\Hero;
use Domain\Board\GameSession\DataObjects\Heroes;
use Domain\Board\GameSession\DataObjects\SaveState;
use Domain\Board\GameSession\DataObjects\Zargon;
use Smorken\Domain\Actions\Action;
use Smorken\Domain\Actions\ActionWithEloquent;
use Smorken\Domain\Authorization\Constants\PolicyType;

/**
 * @template TModel of \Domain\Board\GameSession\Models\Eloquent\Game
 *
 * @extends ActionWithEloquent<TModel>
 */
class SaveStateAction extends Action implements \Domain\Board\GameSession\Contracts\Actions\SaveStateAction
{
    public function __construct(
        protected FindGameRepository $findGameRepository
    ) {
        parent::__construct();
    }

    public function __invoke(SaveState $saveState): Game
    {
        $game = ($this->findGameRepository)($saveState->id);
        $this->authorize($game, PolicyType::UPDATE);
        $game->update($this->getUpdateArray($saveState));
        $this->updateGameHeroes($game, $saveState->heroes);
        $this->cleanup($saveState->id);

        return $game->refresh();
    }

    protected function cleanup(int $id): void
    {
        $this->findGameRepository->setCacheKey($id);
        $this->findGameRepository->reset();
    }

    protected function getUpdateArray(SaveState $saveState): array
    {
        $wanted = [];
        $props = $saveState->toGameModelArray();
        foreach ($props as $key => $value) {
            if (is_object($value) && method_exists($value, 'isEmpty') && $value->isEmpty()) {
                continue;
            }
            $wanted[$key] = $value;
        }

        return $wanted;
    }

    protected function updateGameHero(Game $game, Hero|Zargon $hero, int $i): void
    {
        /** @var \Domain\Board\GameSession\Contracts\Models\GameHero $gameHeroModel */
        $gameHeroModel = $game->gameHeroes->first(function (GameHero $gameHero) use ($hero) {
            return $gameHero->hero_id === $hero->id;
        });
        if (! $gameHeroModel) {
            return;
        }
        $gameHeroModel->update([
            'user_id' => $hero->playerId,
            'order' => $i,
            'body_points' => $hero->stats?->currentBodyPoints ?? 0,
            'x' => $hero->x ?? 0,
            'y' => $hero->y ?? 0,
        ]);
    }

    protected function updateGameHeroes(Game $game, Heroes $heroes): void
    {
        /**
         * @var \Domain\Board\GameSession\DataObjects\Hero|\Domain\Board\GameSession\DataObjects\Zargon $hero
         */
        foreach ($heroes->heroes as $i => $hero) {
            $this->updateGameHero($game, $hero, $i);
        }
    }
}
