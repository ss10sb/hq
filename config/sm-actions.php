<?php

return [
    \Domain\Board\Elements\Heros\Contracts\Actions\DeleteHeroAction::class => \Domain\Board\Elements\Heros\Actions\DeleteHeroAction::class,
    \Domain\Board\Elements\Heros\Contracts\Actions\NewHeroAction::class => \Domain\Board\Elements\Heros\Actions\NewHeroAction::class,
    \Domain\Board\Elements\Heros\Contracts\Actions\SaveHeroAction::class => \Domain\Board\Elements\Heros\Actions\SaveHeroAction::class,

    \Domain\Board\GameBoard\Contracts\Actions\DeleteBoardAction::class => \Domain\Board\GameBoard\Actions\DeleteBoardAction::class,
    \Domain\Board\GameBoard\Contracts\Actions\NewBoardAction::class => \Domain\Board\GameBoard\Actions\NewBoardAction::class,
    \Domain\Board\GameBoard\Contracts\Actions\SaveBoardAction::class => \Domain\Board\GameBoard\Actions\SaveBoardAction::class,

    \Domain\Board\GameSession\Contracts\Actions\DeleteGameAction::class => \Domain\Board\GameSession\Actions\DeleteGameAction::class,
    \Domain\Board\GameSession\Contracts\Actions\NewGameAction::class => \Domain\Board\GameSession\Actions\NewGameAction::class,
    \Domain\Board\GameSession\Contracts\Actions\SetGameStatusAction::class => \Domain\Board\GameSession\Actions\SetGameStatusAction::class,
    \Domain\Board\GameSession\Contracts\Actions\SaveStateAction::class => \Domain\Board\GameSession\Actions\SaveStateAction::class,
];
