<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Monsters\Repositories;

use Domain\Board\Elements\Monsters\Support\DefaultMonstersProvider;
use Illuminate\Support\Collection;
use Smorken\Domain\Repositories\Repository;

class DefaultMonstersRepository extends Repository implements \Domain\Board\Elements\Monsters\Contracts\Repositories\DefaultMonstersRepository
{
    public function __invoke(): Collection
    {
        return DefaultMonstersProvider::get();
    }
}
