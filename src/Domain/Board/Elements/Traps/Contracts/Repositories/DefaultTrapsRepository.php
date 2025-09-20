<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Traps\Contracts\Repositories;

use Illuminate\Support\Collection;
use Smorken\Domain\Repositories\Contracts\Repository;

interface DefaultTrapsRepository extends Repository
{
    /**
     * @template TData of \Domain\Board\Elements\Traps\DataObjects\Trap
     *
     * @return \Illuminate\Support\Collection<array-key, TData>
     */
    public function __invoke(): Collection;
}
