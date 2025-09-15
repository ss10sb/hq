<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Repositories;

use Domain\Board\GameBoard\Contracts\Models\Board;
use Smorken\Domain\Repositories\EloquentRetrieveRepository;

/**
 * @template TModel of \Domain\Board\GameBoard\Models\Eloquent\Board
 *
 * @extends EloquentRetrieveRepository<TModel>
 */
class FindBoardRepository extends EloquentRetrieveRepository implements \Domain\Board\GameBoard\Contracts\Repositories\FindBoardRepository
{
    public function __construct(Board $model)
    {
        parent::__construct($model);
    }
}
