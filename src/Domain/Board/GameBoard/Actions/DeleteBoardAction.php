<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Actions;

use Domain\Board\GameBoard\Contracts\Models\Board;
use Smorken\Domain\Actions\EloquentDeleteAction;

/**
 * @template TModel of \Domain\Board\GameBoard\Models\Eloquent\Board
 *
 * @extends EloquentDeleteAction<TModel>
 */
class DeleteBoardAction extends EloquentDeleteAction implements \Domain\Board\GameBoard\Contracts\Actions\DeleteBoardAction
{
    public function __construct(Board $model)
    {
        parent::__construct($model);
    }
}
