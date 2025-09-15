<?php

Route::middleware(['auth', 'verified'])
    ->prefix('game')
    ->group(function () {
        Route::get('/start', \App\Http\Controllers\Game\StartGameController::class)
            ->name('game.start');
        Route::get('/{sessionId}/join', \App\Http\Controllers\Game\JoinGameController::class)
            ->name('game.join');
    });
