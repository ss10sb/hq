<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Models\Builders;

use Smorken\Model\QueryBuilders\Builder;

/**
 * @template TModel of \Domain\Board\GameBoard\Models\Eloquent\Board
 *
 * @extends Builder<TModel>
 */
class BoardBuilder extends Builder
{
    public function creatorIdIs(int $creatorId): self
    {
        return $this->where('creator_id', '=', $creatorId);
    }

    public function defaultOrder(): Builder
    {
        return $this->orderBy('group', 'asc')
            ->orderBy('order', 'asc');
    }

    public function isPublicIs(bool|int $isPublic): self
    {
        if (is_bool($isPublic)) {
            $isPublic = $isPublic ? 1 : 0;
        }

        return $this->where('is_public', '=', $isPublic);
    }
}
