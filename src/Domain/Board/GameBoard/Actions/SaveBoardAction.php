<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Actions;

use Domain\Board\GameBoard\Contracts\Models\Board;
use Domain\Board\GameBoard\Contracts\Repositories\FindBoardRepository;
use Domain\Board\GameBoard\DataObjects\Board as BoardData;
use Smorken\Domain\Actions\ActionWithEloquent;
use Smorken\Domain\Authorization\Constants\PolicyType;

/**
 * @template TModel of \Domain\Board\GameBoard\Models\Eloquent\Board
 *
 * @extends ActionWithEloquent<TModel>
 */
class SaveBoardAction extends ActionWithEloquent implements \Domain\Board\GameBoard\Contracts\Actions\SaveBoardAction
{
    public function __construct(
        Board $model,
        protected FindBoardRepository $findBoardRepository,
    ) {
        parent::__construct($model);
    }

    public function __invoke(int $id, BoardData $board): Board
    {
        $boardModel = ($this->findBoardRepository)($id);
        $this->authorize($boardModel, PolicyType::UPDATE);
        $data = $board->toModelArray();
        $boardModel->update($data);
        $this->findBoardRepository->setCacheKey($id);
        $this->findBoardRepository->reset();

        /** @var Board */
        return $boardModel->fresh();
    }
}
