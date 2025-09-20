<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Contracts\Repositories;

use Illuminate\Support\Collection;
use Smorken\Domain\Repositories\Contracts\Repository;

interface DefaultHeroesRepository extends Repository
{
    /**
     * @template TData of \Domain\Board\Elements\Heros\DataObjects\Hero
     *
     * @return \Illuminate\Support\Collection<array-key, TData>
     */
    public function __invoke(): Collection;
}
