<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Traps\Repositories;

use Domain\Board\Elements\Traps\Support\DefaultTrapsProvider;
use Illuminate\Support\Collection;
use Smorken\Domain\Repositories\Repository;

class DefaultTrapsRepository extends Repository implements \Domain\Board\Elements\Traps\Contracts\Repositories\DefaultTrapsRepository
{
    public function __invoke(): Collection
    {
        return DefaultTrapsProvider::get();
    }
}
