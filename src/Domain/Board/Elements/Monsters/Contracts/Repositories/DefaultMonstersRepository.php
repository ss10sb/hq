<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Monsters\Contracts\Repositories;

use Illuminate\Support\Collection;
use Smorken\Domain\Repositories\Contracts\Repository;

interface DefaultMonstersRepository extends Repository
{
    /**
     * @template TData of \Domain\Board\Elements\Monsters\DataObjects\Monster
     *
     * @return \Illuminate\Support\Collection<array-key, TData>
     */
    public function __invoke(): Collection;
}
