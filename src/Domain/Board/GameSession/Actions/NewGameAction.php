<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Actions;

use Domain\Board\GameBoard\DataObjects\Board;
use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\Contracts\Models\Game;
use Domain\Board\GameSession\DataObjects\Heroes;
use Domain\Board\GameSession\DataObjects\Zargon;
use Domain\Shared\Contracts\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Smorken\Domain\Actions\ActionWithEloquent;
use Smorken\Domain\Authorization\Constants\PolicyType;

/**
 * @template TModel of \Domain\Board\GameSession\Models\Eloquent\Game
 *
 * @extends ActionWithEloquent<TModel>
 */
class NewGameAction extends ActionWithEloquent implements \Domain\Board\GameSession\Contracts\Actions\NewGameAction
{
    public function __construct(Game $model, #[CurrentUser] protected User $user)
    {
        parent::__construct($model);
    }

    public function __invoke(Board $board, string $joinKey): Game
    {
        $this->authorize(null, PolicyType::CREATE);
        $userId = (int) $this->user->getAuthIdentifier();

        $game = $this->model->newQuery()->create([
            'board_id' => $board->id,
            'join_key' => $joinKey,
            'game_master_id' => $userId,
            'status' => Status::PENDING,
            'max_heroes' => 4,
            'current_hero_id' => 0,
            'elements' => $board->elements,
            'tiles' => $board->tiles,
            'heroes' => new Heroes([
                new Zargon(playerId: $userId),
            ]),
        ]);
        $game->users()->attach($userId);

        return $game;
    }
}
