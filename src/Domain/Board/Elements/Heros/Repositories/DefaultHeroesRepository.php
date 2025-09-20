<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Repositories;

use Domain\Board\Elements\Heros\Support\DefaultHeroesProvider;
use Illuminate\Support\Collection;
use Smorken\Domain\Repositories\Repository;

class DefaultHeroesRepository extends Repository implements \Domain\Board\Elements\Heros\Contracts\Repositories\DefaultHeroesRepository
{
    public function __invoke(): Collection
    {
        return DefaultHeroesProvider::get();
    }
}
