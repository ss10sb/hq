<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Actions;

use Domain\Board\Elements\Heros\Contracts\Models\Hero;
use Domain\Board\Elements\Heros\DataObjects\Hero as HeroData;
use Domain\Board\Elements\Heros\Validation\RuleProviders\NewHeroRules;
use Domain\Shared\Contracts\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Smorken\Domain\Actions\ActionWithEloquent;
use Smorken\Domain\Actions\Concerns\HasValidationFactory;

/**
 * @template TModel of \Domain\Board\Elements\Heros\Models\Eloquent\Hero
 *
 * @extends ActionWithEloquent<TModel>
 */
class NewHeroAction extends ActionWithEloquent implements \Domain\Board\Elements\Heros\Contracts\Actions\NewHeroAction
{
    use HasValidationFactory;

    protected string $rulesProvider = NewHeroRules::class;

    public function __construct(Hero $model, #[CurrentUser] protected User $user)
    {
        parent::__construct($model);
    }

    public function __invoke(HeroData $heroData): Hero
    {
        $attributes = $heroData->toModelArray();
        $this->validate($attributes, $this->fromRulesProvider());
        $attributes['user_id'] = (int) $this->user->getAuthIdentifier();

        return $this->model->newQuery()->create($attributes);
    }
}
