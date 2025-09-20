<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Repositories;

use Domain\Board\GameSession\Contracts\Models\Game;
use Smorken\Domain\Cache\Constants\CacheType;
use Smorken\Domain\Repositories\EloquentRetrieveRepository;

/**
 * @template TModel of \Domain\Board\GameSession\Models\Eloquent\Game
 *
 * @extends EloquentRetrieveRepository<TModel>
 */
class FindGameRepository extends EloquentRetrieveRepository implements \Domain\Board\GameSession\Contracts\Repositories\FindGameRepository
{
    protected CacheType $cacheType = CacheType::INSTANCE;

    public function __construct(Game $model)
    {
        parent::__construct($model);
    }
}
