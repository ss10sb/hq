<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Repositories;

use Domain\Board\GameSession\Contracts\Models\Game;
use Domain\Shared\Contracts\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Smorken\Domain\Repositories\EloquentFilteredRepository;

/**
 * @template TModel of \Domain\Board\GameSession\Models\Eloquent\Game
 *
 * @extends EloquentFilteredRepository<TModel>
 */
class MyFilteredGamesRepository extends EloquentFilteredRepository implements \Domain\Board\GameSession\Contracts\Repositories\MyFilteredGamesRepository
{
    public function __construct(Game $model, #[CurrentUser] protected User $user)
    {
        parent::__construct($model);
    }

    /**
     * @param  \Domain\Board\GameSession\Models\Builders\GameBuilder  $query
     * @return \Domain\Board\GameSession\Models\Builders\GameBuilder
     */
    protected function modifyQuery(Builder $query): Builder
    {
        return $query->hasUserId((int) $this->user->getAuthIdentifier())->latest();
    }
}
