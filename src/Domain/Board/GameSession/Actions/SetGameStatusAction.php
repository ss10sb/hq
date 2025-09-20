<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Actions;

use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\Contracts\Models\Game;
use Smorken\Domain\Actions\ActionWithEloquent;
use Smorken\Domain\Authorization\Constants\PolicyType;

/**
 * @template TModel of \Domain\Board\GameSession\Models\Eloquent\Game
 *
 * @extends ActionWithEloquent<TModel>
 */
class SetGameStatusAction extends ActionWithEloquent implements \Domain\Board\GameSession\Contracts\Actions\SetGameStatusAction
{
    public function __construct(Game $model)
    {
        parent::__construct($model);
    }

    public function __invoke(Game $game, Status $status): Game
    {
        $this->authorize($game, PolicyType::UPDATE);
        $game->update(['status' => $status]);

        return $game->fresh();
    }
}
