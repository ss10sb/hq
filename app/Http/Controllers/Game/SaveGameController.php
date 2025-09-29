<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Domain\Board\GameSession\Contracts\Services\SaveStateService;
use Domain\Board\GameSession\DataObjects\SaveState;
use Illuminate\Http\Request;

class SaveGameController
{
    public function __construct(
        protected SaveStateService $saveStateService
    ) {}

    public function __invoke(Request $request, int $id): void
    {
        ($this->saveStateService)(
            SaveState::fromRequest($request, $id)
        );
    }
}
