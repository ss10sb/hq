<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StartGameController
{
    public function __invoke(): RedirectResponse
    {
        // TODO generate game session id and redirect to JoinGameController as the game master
        return Redirect::route('game.join', ['sessionId' => '']);
    }
}
