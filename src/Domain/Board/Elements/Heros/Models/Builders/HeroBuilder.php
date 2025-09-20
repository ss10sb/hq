<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Models\Builders;

use Smorken\Model\QueryBuilders\Builder;

/**
 * @template TModel of \Domain\Board\Elements\Heros\Models\Eloquent\Hero
 *
 * @extends Builder<TModel>
 */
class HeroBuilder extends Builder
{
    public function defaultOrder(): Builder
    {
        return $this->orderBy('name');
    }

    public function userIdIs(int $id): self
    {
        return $this->where('user_id', '=', $id);
    }
}
