<?php

declare(strict_types=1);

use Domain\Board\GameBoard\Models\Eloquent\Board;
use Domain\Shared\Models\Eloquent\User;

it('toggles the edit sidebar and does not block canvas interactions', function () {
    // Create a verified user and a board owned by that user
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $board = Board::factory()->create([
        'creator_id' => $user->id,
    ]);

    // Authenticate the user for the browser session
    $this->actingAs($user);

    // Visit the board edit page
    $page = visit(route('board.edit', $board->id));

    // Sidebar should be visible by default
    $page->assertSee('Tools');

    // Close the sidebar using dusk selector
    $page->click('@sidebar-close');

    // Open the sidebar using the floating button at top right
    $page->click('@sidebar-open');

    // Verify it opened again
    $page->assertSee('Tools');

    // Attempt an interaction on the canvas area to ensure no blocking overlay
    $page->click('.board-canvas-container')
         ->assertNoJavascriptErrors();
});
