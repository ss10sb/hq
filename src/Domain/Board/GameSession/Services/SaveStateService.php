<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Services;

use Domain\Board\Elements\Heros\Contracts\Actions\SaveHeroAction;
use Domain\Board\Elements\Heros\DataObjects\Hero;
use Domain\Board\GameSession\Contracts\Actions\SaveStateAction;
use Domain\Board\GameSession\DataObjects\SaveState;

class SaveStateService implements \Domain\Board\GameSession\Contracts\Services\SaveStateService
{
    public function __construct(
        protected SaveStateAction $saveStateAction,
        protected SaveHeroAction $saveHeroAction,
    ) {}

    public function __invoke(SaveState $state, bool $updateHeroes = false): bool
    {
        ($this->saveStateAction)($state);
        if (! $updateHeroes) {
            return true;
        }
        /** @var \Domain\Board\GameSession\DataObjects\Hero $hero */
        foreach ($state->heroes->heroes as $hero) {
            ($this->saveHeroAction)($hero->id, Hero::fromGameHeroData($hero));
        }

        return true;
    }
}
