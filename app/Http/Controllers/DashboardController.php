<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Board\GameSession\Contracts\Repositories\MyFilteredGamesRepository;
use Domain\Board\GameSession\Resources\MyGameResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Smorken\QueryStringFilter\Contracts\QueryStringFilter;

class DashboardController
{
    public function __construct(
        protected MyFilteredGamesRepository $myFilteredGamesRepository,
    ) {}

    public function __invoke(Request $request): Response
    {
        $filter = $this->getFilter($request);
        $games = ($this->myFilteredGamesRepository)($filter, 10);

        return Inertia::render('Dashboard', [
            'games' => MyGameResource::collection($games),
        ]);
    }

    protected function getFilter(Request $request): QueryStringFilter
    {
        return \Smorken\QueryStringFilter\QueryStringFilter::from($request)
            ->setFilters([])
            ->addKeyValue($this->myFilteredGamesRepository->getPageName());
    }
}
