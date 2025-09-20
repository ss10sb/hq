<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Factories;

use Domain\Board\Elements\Heros\Contracts\Repositories\DefaultHeroesRepository;
use Domain\Board\Elements\Monsters\Contracts\Repositories\DefaultMonstersRepository;
use Domain\Board\Elements\Traps\Contracts\Repositories\DefaultTrapsRepository;
use Domain\Board\GameBoard\Fixtures\Contracts\Repositories\DefaultFixturesRepository;
use Illuminate\Support\Collection;
use Smorken\Domain\Factories\RepositoryFactory;

class DefaultsRepositoryFactory extends RepositoryFactory
{
    protected array $handlers = [
        'heroes' => DefaultHeroesRepository::class,
        'monsters' => DefaultMonstersRepository::class,
        'traps' => DefaultTrapsRepository::class,
        'fixtures' => DefaultFixturesRepository::class,
    ];

    /**
     * @return \Illuminate\Support\Collection<array-key, \Domain\Board\GameBoard\Fixtures\DataObjects\Fixture>
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function fixtures(): Collection
    {
        $repository = $this->make($this->getHandler('fixtures'));

        return $repository();
    }

    /**
     * @return \Illuminate\Support\Collection<array-key, \Domain\Board\Elements\Heros\DataObjects\Hero>
     */
    public function heroes(): Collection
    {
        $repository = $this->make($this->getHandler('heroes'));

        return $repository();
    }

    /**
     * @return \Illuminate\Support\Collection<array-key, \Domain\Board\Elements\Monsters\DataObjects\Monster>
     */
    public function monsters(): Collection
    {
        $repository = $this->make($this->getHandler('monsters'));

        return $repository();
    }

    /**
     * @return \Illuminate\Support\Collection<array-key, \Domain\Board\Elements\Traps\DataObjects\Trap>
     */
    public function traps(): Collection
    {
        $repository = $this->make($this->getHandler('traps'));

        return $repository();
    }
}
