<?php

return [
    \Domain\Board\Elements\Heros\Contracts\Repositories\DefaultHeroesRepository::class => \Domain\Board\Elements\Heros\Repositories\DefaultHeroesRepository::class,
    \Domain\Board\Elements\Heros\Contracts\Repositories\FindHeroRepository::class => \Domain\Board\Elements\Heros\Repositories\FindHeroRepository::class,
    \Domain\Board\Elements\Heros\Contracts\Repositories\MyHeroesRepository::class => \Domain\Board\Elements\Heros\Repositories\MyHeroesRepository::class,
    \Domain\Board\Elements\Monsters\Contracts\Repositories\DefaultMonstersRepository::class => \Domain\Board\Elements\Monsters\Repositories\DefaultMonstersRepository::class,
    \Domain\Board\Elements\Traps\Contracts\Repositories\DefaultTrapsRepository::class => \Domain\Board\Elements\Traps\Repositories\DefaultTrapsRepository::class,

    \Domain\Board\GameBoard\Contracts\Repositories\FindBoardRepository::class => \Domain\Board\GameBoard\Repositories\FindBoardRepository::class,
    \Domain\Board\GameBoard\Contracts\Repositories\MyBoardsRepository::class => \Domain\Board\GameBoard\Repositories\MyBoardsRepository::class,
    \Domain\Board\GameBoard\Contracts\Repositories\PublicBoardsRepository::class => \Domain\Board\GameBoard\Repositories\PublicBoardsRepository::class,
    \Domain\Board\GameBoard\Fixtures\Contracts\Repositories\DefaultFixturesRepository::class => \Domain\Board\GameBoard\Fixtures\Repositories\DefaultFixturesRepository::class,

    \Domain\Board\GameSession\Contracts\Repositories\FindAvailableGameRepository::class => \Domain\Board\GameSession\Repositories\FindAvailableGameRepository::class,
    \Domain\Board\GameSession\Contracts\Repositories\FindGameRepository::class => \Domain\Board\GameSession\Repositories\FindGameRepository::class,
    \Domain\Board\GameSession\Contracts\Repositories\JoinKeyExistsRepository::class => \Domain\Board\GameSession\Repositories\JoinKeyExistsRepository::class,
    \Domain\Board\GameSession\Contracts\Repositories\MyFilteredGamesRepository::class => \Domain\Board\GameSession\Repositories\MyFilteredGamesRepository::class,

];
