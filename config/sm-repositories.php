<?php

return [

    \Domain\Board\GameBoard\Contracts\Repositories\FindBoardRepository::class => \Domain\Board\GameBoard\Repositories\FindBoardRepository::class,
    \Domain\Board\GameBoard\Contracts\Repositories\MyBoardsRepository::class => \Domain\Board\GameBoard\Repositories\MyBoardsRepository::class,
    \Domain\Board\GameBoard\Contracts\Repositories\PublicBoardsRepository::class => \Domain\Board\GameBoard\Repositories\PublicBoardsRepository::class,
];
