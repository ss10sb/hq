<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Repositories;

use Smorken\Domain\Repositories\Contracts\Repository;

interface JoinKeyExistsRepository extends Repository
{
    public function __invoke(string $joinKey): bool;
}
