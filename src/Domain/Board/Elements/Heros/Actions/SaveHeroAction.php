<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Actions;

use Domain\Board\Elements\Heros\Contracts\Models\Hero;
use Domain\Board\Elements\Heros\DataObjects\Hero as HeroData;
use Domain\Board\Elements\Heros\Validation\RuleProviders\UpdateHeroRules;
use Smorken\Domain\Actions\ActionWithEloquent;
use Smorken\Domain\Actions\Concerns\HasValidationFactory;
use Smorken\Domain\Authorization\Constants\PolicyType;

/**
 * @template TModel of \Domain\Board\Elements\Heros\Models\Eloquent\Hero
 *
 * @extends ActionWithEloquent<TModel>
 */
class SaveHeroAction extends ActionWithEloquent implements \Domain\Board\Elements\Heros\Contracts\Actions\SaveHeroAction
{
    use HasValidationFactory;

    protected string $rulesProvider = UpdateHeroRules::class;

    public function __construct(Hero $model)
    {
        parent::__construct($model);
    }

    public function __invoke(int $id, HeroData $heroData, bool $authorize = true): Hero
    {
        $hero = $this->model->newQuery()->findOrFail($id);
        if ($authorize) {
            $this->authorize($hero, PolicyType::UPDATE);
        }
        $attributes = $heroData->toModelArray();
        $this->validate($attributes, $this->fromRulesProvider());
        $hero->update($attributes);

        /** @var Hero */
        return $hero->fresh();
    }
}
