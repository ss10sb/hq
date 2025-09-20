<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Models\Builders;

use Domain\Board\GameSession\Constants\Status;
use Smorken\Model\QueryBuilders\Builder;

/**
 * @template TModel of \Domain\Board\GameSession\Models\Eloquent\Game
 *
 * @extends Builder<TModel>
 */
class GameBuilder extends Builder
{
    public function boardIdIs(int $id): self
    {
        return $this->where('board_id', '=', $id);
    }

    public function gameMasterIdIs(int $id): self
    {
        return $this->where('game_master_id', '=', $id);
    }

    public function hasUserId(int $userId): self
    {
        return $this->where(function ($query) use ($userId) {
            $query->gameMasterIdIs($userId)
                ->orWhereHas('users', function ($query) use ($userId) {
                    $query->where('users.id', '=', $userId);
                });
        });
    }

    public function isAvailable(): self
    {
        return $this->whereIn('status', [Status::PENDING, Status::IN_PROGRESS]);
    }

    public function isNotAvailable(): self
    {
        return $this->whereIn('status', [Status::COMPLETED, Status::ABORTED]);
    }

    public function isPending(): self
    {
        return $this->statusIs(Status::PENDING);
    }

    public function joinKeyIs(string $key): self
    {
        return $this->where('join_key', '=', $key);
    }

    public function statusIs(Status|string $status): self
    {
        if (is_a($status, Status::class)) {
            $status = $status->value;
        }

        return $this->where('status', '=', $status);
    }
}
