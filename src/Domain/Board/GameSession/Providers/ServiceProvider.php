<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Providers;

use Domain\Board\GameSession\Contracts\Generators\JoinKeyGenerator;
use Domain\Board\GameSession\Contracts\Services\JoinGameService;
use Domain\Board\GameSession\Contracts\Services\SaveStateService;
use Domain\Board\GameSession\Contracts\Services\StartNewGameService;
use Domain\Board\GameSession\Generators\SimpleCharacterListGenerator;
use Domain\Board\GameSession\Models\Eloquent\Game;
use Domain\Board\GameSession\Policies\GamePolicy;
use Illuminate\Contracts\Auth\Access\Gate;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
        GamePolicy::defineSelf(Game::class, $this->app[Gate::class]);
    }

    public function register(): void
    {
        $this->registerJoinKeyGenerator();
        $this->registerStartNewGameService();
        $this->registerJoinGameService();
        $this->registerSaveStateService();
    }

    protected function registerJoinGameService(): void
    {
        $this->app->scoped(JoinGameService::class, \Domain\Board\GameSession\Services\JoinGameService::class);
    }

    protected function registerJoinKeyGenerator(): void
    {
        $this->app->scoped(JoinKeyGenerator::class, SimpleCharacterListGenerator::class);
    }

    protected function registerSaveStateService(): void
    {
        $this->app->scoped(SaveStateService::class, \Domain\Board\GameSession\Services\SaveStateService::class);
    }

    protected function registerStartNewGameService(): void
    {
        $this->app->scoped(StartNewGameService::class, \Domain\Board\GameSession\Services\StartNewGameService::class);
    }
}
