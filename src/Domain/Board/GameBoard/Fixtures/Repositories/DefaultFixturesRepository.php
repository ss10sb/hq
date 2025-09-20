<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Fixtures\Repositories;

use Domain\Board\GameBoard\Fixtures\Support\DefaultFixturesProvider;
use Illuminate\Support\Collection;
use Smorken\Domain\Repositories\Repository;

class DefaultFixturesRepository extends Repository implements \Domain\Board\GameBoard\Fixtures\Contracts\Repositories\DefaultFixturesRepository
{
    public function __invoke(): Collection
    {
        return DefaultFixturesProvider::get();
    }
}
