<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Actions;

use Domain\Board\GameSession\Contracts\Models\Game;
use Domain\Board\GameSession\Contracts\Repositories\FindGameRepository;
use Domain\Board\GameSession\DataObjects\SaveState;
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
        $props = $saveState->toModelArray();
        foreach ($props as $key => $value) {
            if (is_object($value) && method_exists($value, 'isEmpty') && $value->isEmpty()) {
                continue;
            }
            $wanted[$key] = $value;
        }

        return $wanted;
    }
}
