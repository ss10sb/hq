<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\Contracts\Actions\SaveStateAction;
use Domain\Board\GameSession\Contracts\Actions\SetGameStatusAction;
use Domain\Board\GameSession\DataObjects\SaveState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CompleteGameController
{
    public function __construct(
        protected SaveStateAction $saveStateAction,
        protected SetGameStatusAction $setGameStatusAction,
    ) {}

    public function __invoke(Request $request, int $id): RedirectResponse
    {
        $game = ($this->saveStateAction)(
            SaveState::fromRequest($request, $id)
        );
        ($this->setGameStatusAction)($game, Status::COMPLETED);

        return Redirect::route('dashboard', [], 303)->with('success', 'Game completed!');
    }
}
