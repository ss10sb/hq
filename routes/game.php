<?php

Route::middleware(['auth', 'verified'])
    ->prefix('game')
    ->group(function () {
        Route::get('/start/{boardId}', \App\Http\Controllers\Game\NewGameController::class)
            ->name('game.start');
        Route::get('/{heroId}/{joinKey}/join', \App\Http\Controllers\Game\JoinGameController::class)
            ->name('game.join');
        Route::delete('/{id}', \App\Http\Controllers\Game\DeleteGameController::class)
            ->name('game.delete');
        Route::get('/{id}/waiting-room', \App\Http\Controllers\Game\WaitingRoomController::class)
            ->name('game.waiting-room');
        Route::get('/{id}/play', \App\Http\Controllers\Game\PlayGameController::class)
            ->name('game.play');
        Route::put('/{id}/play', \App\Http\Controllers\Game\SaveGameController::class)
            ->name('game.play.save');
        Route::put('/{id}/complete', \App\Http\Controllers\Game\CompleteGameController::class)
            ->name('game.complete');
    });
