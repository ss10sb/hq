<?php

declare(strict_types=1);

namespace App\Http\Controllers\Hero;

use Domain\Board\Elements\Factories\DefaultsRepositoryFactory;
use Domain\Board\Elements\Heros\Contracts\Actions\DeleteHeroAction;
use Domain\Board\Elements\Heros\Contracts\Actions\NewHeroAction;
use Domain\Board\Elements\Heros\Contracts\Actions\SaveHeroAction;
use Domain\Board\Elements\Heros\Contracts\Repositories\MyHeroesRepository;
use Domain\Board\Elements\Heros\DataObjects\Hero;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SelectHeroController
{
    public function __construct(
        protected MyHeroesRepository $myHeroesRepository,
        protected DefaultsRepositoryFactory $defaultsRepositoryFactory,
        protected NewHeroAction $newHeroAction,
        protected SaveHeroAction $saveHeroAction,
        protected DeleteHeroAction $deleteHeroAction,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('hero/Select', [
            'myHeroes' => ($this->myHeroesRepository)(10),
            'heroes' => $this->defaultsRepositoryFactory->heroes(),
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $hero = Hero::fromRequest($request);
        ($this->newHeroAction)($hero);

        return Redirect::route('hero.select');
    }

    public function destroy(int $id): RedirectResponse
    {
        ($this->deleteHeroAction)($id);

        return Redirect::route('hero.select');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $hero = Hero::fromRequest($request, $id);
        ($this->saveHeroAction)($id, $hero);

        return Redirect::route('hero.select');
    }
}
