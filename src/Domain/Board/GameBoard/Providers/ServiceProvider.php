<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Providers;

use Domain\Board\GameBoard\Actions\NewBoardAction;
use Domain\Board\GameBoard\Models\Eloquent\Board;
use Domain\Board\GameBoard\Policies\BoardPolicy;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
        NewBoardAction::setValidationFactory($this->app[Factory::class]);
        BoardPolicy::defineSelf(Board::class, $this->app[Gate::class]);
    }
}
