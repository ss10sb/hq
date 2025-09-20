<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Providers;

use Domain\Board\Elements\Heros\Actions\NewHeroAction;
use Domain\Board\Elements\Heros\Actions\SaveHeroAction;
use Domain\Board\Elements\Heros\Models\Eloquent\Hero;
use Domain\Board\Elements\Heros\Policies\HeroPolicy;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
        $validator = $this->app[Factory::class];
        NewHeroAction::setValidationFactory($validator);
        SaveHeroAction::setValidationFactory($validator);
        HeroPolicy::defineSelf(Hero::class, $this->app[Gate::class]);
    }
}
