<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Repositories;

use Domain\Board\GameBoard\Contracts\Models\Board;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Smorken\Domain\Repositories\EloquentIterableRepository;

/**
 * @template TModel of \Domain\Board\GameBoard\Models\Eloquent\Board
 *
 * @extends EloquentIterableRepository<TModel>
 */
class PublicBoardsRepository extends EloquentIterableRepository implements \Domain\Board\GameBoard\Contracts\Repositories\PublicBoardsRepository
{
    protected string $pageName = 'pbPage';

    public function __construct(Board $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  \Domain\Board\GameBoard\Models\Builders\BoardBuilder  $query
     * @return \Domain\Board\GameBoard\Models\Builders\BoardBuilder
     */
    protected function modifyQuery(Builder $query): Builder
    {
        return $query->isPublicIs(true);
    }
}
