<?php

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/hero', \App\Http\Controllers\Hero\SelectHeroController::class)
            ->name('hero.select');
        Route::post('/hero', [\App\Http\Controllers\Hero\SelectHeroController::class, 'create'])
            ->name('hero.create');
        Route::put('/hero/{id}', [\App\Http\Controllers\Hero\SelectHeroController::class, 'update'])
            ->name('hero.update');
        Route::delete('/hero/{id}', [\App\Http\Controllers\Hero\SelectHeroController::class, 'destroy'])
            ->name('hero.destroy');
    });
