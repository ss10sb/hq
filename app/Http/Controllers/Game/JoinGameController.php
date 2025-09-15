<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Inertia\Inertia;
use Inertia\Response;

class JoinGameController
{
    public function __invoke(string $sessionId): Response
    {
        return Inertia::render('game/Join', [
            'sessionId' => $sessionId,
        ]);
    }
}
