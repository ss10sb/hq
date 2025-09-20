<?php

return [
    \Domain\Board\Elements\Heros\Contracts\Models\Hero::class => \Domain\Board\Elements\Heros\Models\Eloquent\Hero::class,

    \Domain\Board\GameBoard\Contracts\Models\Board::class => \Domain\Board\GameBoard\Models\Eloquent\Board::class,

    \Domain\Board\GameSession\Contracts\Models\Game::class => \Domain\Board\GameSession\Models\Eloquent\Game::class,

    \Domain\Shared\Contracts\Models\User::class => \Domain\Shared\Models\Eloquent\User::class,
];
