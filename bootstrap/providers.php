<?php

return [
    App\Providers\AppServiceProvider::class,

    Domain\Board\Elements\Providers\ServiceProvider::class,
    Domain\Board\GameBoard\Providers\ServiceProvider::class,
    Domain\Board\GameSession\Providers\ServiceProvider::class,
];
