<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Contracts\Actions;

use Domain\Board\Elements\Heros\Contracts\Models\Hero;
use Domain\Board\Elements\Heros\DataObjects\Hero as HeroData;
use Smorken\Domain\Actions\Contracts\Action;

interface SaveHeroAction extends Action
{
    public function __invoke(int $id, HeroData $heroData): Hero;
}
