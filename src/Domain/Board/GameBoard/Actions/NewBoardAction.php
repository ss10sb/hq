<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Actions;

use Domain\Board\GameBoard\Contracts\Models\Board;
use Domain\Board\GameBoard\DataObjects\Board as BoardData;
use Domain\Board\GameBoard\Support\TilesGenerator;
use Domain\Board\GameBoard\Validation\RuleProviders\NewBoardRules;
use Domain\Shared\Contracts\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Smorken\Domain\Actions\ActionWithEloquent;
use Smorken\Domain\Actions\Concerns\HasValidationFactory;

class NewBoardAction extends ActionWithEloquent implements \Domain\Board\GameBoard\Contracts\Actions\NewBoardAction
{
    use HasValidationFactory;

    protected string $rulesProvider = NewBoardRules::class;

    public function __construct(Board $model, #[CurrentUser] protected User $user)
    {
        parent::__construct($model);
    }

    public function __invoke(BoardData $newBoard): Board
    {
        $data = $newBoard->toModelArray();
        $this->validate($data, $this->fromRulesProvider());
        $data['creator_id'] = $this->user->id;
        $data['tiles'] = $this->generateTiles($newBoard);

        return $this->model->newQuery()->create($data);
    }

    protected function generateTiles(BoardData $newBoard): array
    {
        return TilesGenerator::generate($newBoard->height, $newBoard->width);
    }
}
