<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Actions;

use Domain\Board\Elements\Heros\Contracts\Models\Hero;
use Smorken\Domain\Actions\EloquentDeleteAction;

/**
 * @template TModel of \Domain\Board\Elements\Heros\Models\Eloquent\Hero
 *
 * @extends EloquentDeleteAction<TModel>
 */
class DeleteHeroAction extends EloquentDeleteAction implements \Domain\Board\Elements\Heros\Contracts\Actions\DeleteHeroAction
{
    public function __construct(Hero $model)
    {
        parent::__construct($model);
    }
}
