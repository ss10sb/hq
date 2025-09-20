<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Repositories;

use Domain\Board\GameSession\Contracts\Models\Game;
use Smorken\Domain\Repositories\Contracts\Repository;

interface FindAvailableGameRepository extends Repository
{
    public function __invoke(string $joinKey): ?Game;
}
