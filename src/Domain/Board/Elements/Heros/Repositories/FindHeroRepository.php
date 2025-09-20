<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Repositories;

use Domain\Board\Elements\Heros\Contracts\Models\Hero;
use Smorken\Domain\Repositories\EloquentRetrieveRepository;

/**
 * @template TModel of \Domain\Board\Elements\Heros\Models\Eloquent\Hero
 *
 * @extends EloquentRetrieveRepository<TModel>
 */
class FindHeroRepository extends EloquentRetrieveRepository implements \Domain\Board\Elements\Heros\Contracts\Repositories\FindHeroRepository
{
    public function __construct(Hero $model)
    {
        parent::__construct($model);
    }
}
