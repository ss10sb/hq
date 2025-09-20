<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Actions;

use Domain\Board\GameSession\Contracts\Models\Game;
use Smorken\Domain\Actions\EloquentDeleteAction;

/**
 * @template TModel of \Domain\Board\GameSession\Models\Eloquent\Game
 *
 * @extends EloquentDeleteAction<TModel>
 */
class DeleteGameAction extends EloquentDeleteAction implements \Domain\Board\GameSession\Contracts\Actions\DeleteGameAction
{
    public function __construct(Game $model)
    {
        parent::__construct($model);
    }
}
