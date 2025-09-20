<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Domain\Board\GameSession\Contracts\Actions\SaveStateAction;
use Domain\Board\GameSession\DataObjects\SaveState;
use Illuminate\Http\Request;

class SaveGameController
{
    public function __construct(
        protected SaveStateAction $saveStateAction,
    ) {}

    public function __invoke(Request $request, int $id): void
    {
        ($this->saveStateAction)(
            SaveState::fromRequest($request, $id)
        );
    }
}
