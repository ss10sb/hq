<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Repositories;

use Domain\Board\GameBoard\Contracts\Models\Board;
use Domain\Shared\Contracts\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Smorken\Domain\Repositories\EloquentIterableRepository;

class MyBoardsRepository extends EloquentIterableRepository implements \Domain\Board\GameBoard\Contracts\Repositories\MyBoardsRepository
{
    protected string $pageName = 'mbPage';

    public function __construct(Board $model, #[CurrentUser] protected User $user)
    {
        parent::__construct($model);
    }

    /**
     * @param  \Domain\Board\GameBoard\Models\Builders\BoardBuilder  $query
     * @return \Domain\Board\GameBoard\Models\Builders\BoardBuilder
     */
    protected function modifyQuery(Builder $query): Builder
    {
        return $query->creatorIdIs((int) $this->user->getAuthIdentifier());
    }
}
