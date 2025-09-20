<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Repositories;

use Domain\Board\Elements\Heros\Contracts\Models\Hero;
use Domain\Shared\Contracts\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Smorken\Domain\Repositories\EloquentIterableRepository;

/**
 * @template TModel of \Domain\Board\Elements\Heros\Contracts\Models\Hero
 *
 * @extends EloquentIterableRepository<TModel>
 */
class MyHeroesRepository extends EloquentIterableRepository implements \Domain\Board\Elements\Heros\Contracts\Repositories\MyHeroesRepository
{
    public function __construct(Hero $model, #[CurrentUser] protected User $user)
    {
        parent::__construct($model);
    }

    protected function modifyQuery(Builder $query): Builder
    {
        return $query->userIdIs((int) $this->user->getAuthIdentifier());
    }
}
