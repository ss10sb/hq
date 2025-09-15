<?php

declare(strict_types=1);

namespace App\Http\Controllers\Board;

use Domain\Board\GameBoard\Contracts\Repositories\MyBoardsRepository;
use Domain\Board\GameBoard\Contracts\Repositories\PublicBoardsRepository;
use Inertia\Inertia;
use Inertia\Response;

class SelectBoardController
{
    public function __construct(
        protected MyBoardsRepository $myBoardsRepository,
        protected PublicBoardsRepository $publicBoardsRepository,
    ) {}

    public function __invoke(): Response
    {
        $publicBoards = ($this->publicBoardsRepository)(10);
        $myBoards = ($this->myBoardsRepository)(10);

        return Inertia::render('board/Select',
            [
                'publicBoards' => $publicBoards,
                'myBoards' => $myBoards,
            ]
        );
    }
}
