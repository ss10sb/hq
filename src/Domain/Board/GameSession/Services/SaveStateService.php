<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Services;

use Domain\Board\Elements\Heros\Contracts\Actions\SaveHeroAction;
use Domain\Board\Elements\Heros\DataObjects\Hero;
use Domain\Board\GameSession\Contracts\Actions\SaveStateAction;
use Domain\Board\GameSession\Contracts\Models\Game;
use Domain\Board\GameSession\DataObjects\Character;
use Domain\Board\GameSession\DataObjects\SaveState;

class SaveStateService implements \Domain\Board\GameSession\Contracts\Services\SaveStateService
{
    public function __construct(
        protected SaveStateAction $saveStateAction,
        protected SaveHeroAction $saveHeroAction,
    ) {}

    public function __invoke(SaveState $state, bool $updateHeroes = false): Game
    {
        $game = ($this->saveStateAction)($state);
        $this->ensureHeroesSynced($state, $game);
        if (! $updateHeroes) {
            return $game;
        }
        /** @var \Domain\Board\GameSession\DataObjects\Hero $hero */
        foreach ($state->heroes->heroes as $hero) {
            if ($hero->id === 0) {
                continue; // Zargon
            }
            ($this->saveHeroAction)($hero->id, Hero::fromGameHeroData($hero), false);
        }

        return $game;
    }

    protected function ensureHeroesSynced(SaveState $state, Game $game): void
    {
        $heroIds = $state->heroes->heroes->map(fn (Character $hero) => $hero->id)
            ->toArray();
        /** @var \Domain\Board\GameSession\Contracts\Models\GameHero $gameHero */
        foreach ($game->gameHeroes as $gameHero) {
            if (! in_array($gameHero->hero_id, $heroIds)) {
                $gameHero->delete();
            }
        }
    }
}
