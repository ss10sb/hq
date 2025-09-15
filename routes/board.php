<?php

use function Pest\Laravel\get;

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/board/new', \App\Http\Controllers\Board\NewBoardController::class)
            ->name('board.new.index');
        Route::post('/board/new', [\App\Http\Controllers\Board\NewBoardController::class, 'store'])
            ->name('board.new.store');
        Route::get('/board/{id}', \App\Http\Controllers\Board\EditBoardController::class)
            ->name('board.edit');
        Route::put('/board/{id}', [\App\Http\Controllers\Board\EditBoardController::class, 'store'])
            ->name('board.edit.store');

        Route::get('/board', \App\Http\Controllers\Board\SelectBoardController::class)
            ->name('board.select');
    });
