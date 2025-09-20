<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Repositories;

use Domain\Board\GameSession\Contracts\Models\Game;
use Smorken\Domain\Repositories\Repository;

/**
 * @template TModel of \Domain\Board\GameSession\Models\Eloquent\Game
 *
 * @extends Repository<TModel>
 */
class JoinKeyExistsRepository extends Repository implements \Domain\Board\GameSession\Contracts\Repositories\JoinKeyExistsRepository
{
    public function __construct(protected Game $model) {}

    public function __invoke(string $joinKey): bool
    {
        return $this->model->newQuery()
            ->joinKeyIs($joinKey)
            ->isAvailable()
            ->exists();
    }
}
