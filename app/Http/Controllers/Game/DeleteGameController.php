<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Domain\Board\GameSession\Contracts\Actions\DeleteGameAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class DeleteGameController
{
    public function __construct(
        protected DeleteGameAction $deleteGameAction,
    ) {}

    public function __invoke(int $id): RedirectResponse
    {
        ($this->deleteGameAction)($id);

        return Redirect::route('dashboard')->with('success', 'Game deleted successfully');
    }
}
