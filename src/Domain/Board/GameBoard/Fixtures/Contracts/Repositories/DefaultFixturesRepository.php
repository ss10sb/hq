<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Fixtures\Contracts\Repositories;

use Illuminate\Support\Collection;
use Smorken\Domain\Repositories\Contracts\Repository;

interface DefaultFixturesRepository extends Repository
{
    /**
     * @return \Illuminate\Support\Collection<array-key, \Domain\Board\GameBoard\Fixtures\DataObjects\Fixture>
     */
    public function __invoke(): Collection;
}
