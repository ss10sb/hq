<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Repositories;

use Domain\Board\GameSession\Contracts\Models\Game;
use Smorken\Domain\Repositories\Repository;

class FindAvailableGameRepository extends Repository implements \Domain\Board\GameSession\Contracts\Repositories\FindAvailableGameRepository
{
    public function __construct(
        protected Game $model,
    ) {}

    public function __invoke(string $joinKey): ?Game
    {
        return $this->model->newQuery()
            ->joinKeyIs($joinKey)
            ->isAvailable()
            ->latest()
            ->first();
    }
}
