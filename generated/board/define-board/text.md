### **1. Core Concept: The Board as a Data Grid**

The fundamental principle is that the game board is a grid of data. The visual representation on the frontend is a direct reflection of this data grid. This is crucial for enabling real-time synchronization between players and the game master using WebSockets in a future step.

### **2. Frontend Implementation**

The frontend stack handles the user interface, rendering, and state management.

#### **2.1. TypeScript Interfaces**

Defining these interfaces first ensures a single source of truth for the data structure shared between the frontend and the backend.

```typescript
// src/types/board.d.ts

/**
 * An enum representing the different types of tiles on the board.
 * This is easily extendable for future features like doors, traps, etc.
 */
export enum TileType {
  Wall = 'wall',   // Non-interactive, default state for an empty board.
  Floor = 'floor', // Interactive, can contain characters, items, etc.
}

/**
 * The data structure for a single square tile on the board.
 */
export interface Tile {
  id: string; // A unique identifier, e.g., '0_0', '1_2'.
  x: number;  // The column index.
  y: number;  // The row index.
  type: TileType; // The type of the tile (wall, floor, etc.).
  // Future fields for traps, monsters, items, etc. can be added here.
  // E.g., monster?: Monster;
}

/**
 * The full state of the game board. This is the object that will be
 * hydrated from the backend API.
 */
export interface BoardState {
  width: number;
  height: number;
  tiles: Tile[][]; // A 2D array representing the grid of tiles.
}
```

#### **2.2. Pinia State Management**

Pinia will hold the reactive state of the board, allowing any component to access and modify it.

```typescript
// src/stores/board.ts

import { defineStore } from 'pinia';
import { BoardState, Tile, TileType } from '@/types/board';

export const useBoardStore = defineStore('board', {
  state: (): BoardState => ({
    width: 22, // Default width
    height: 28, // Default height
    tiles: [], // An empty 2D array initially
  }),

  actions: {
    /**
     * Initializes a new board with all tiles set to 'wall' type.
     * @param width The number of columns.
     * @param height The number of rows.
     */
    initializeBoard(width: number, height: number) {
      this.width = width;
      this.height = height;
      const newTiles: Tile[][] = [];
      for (let y = 0; y < height; y++) {
        const row: Tile[] = [];
        for (let x = 0; x < width; x++) {
          row.push({
            id: `${x}_${y}`,
            x,
            y,
            type: TileType.Wall,
          });
        }
        newTiles.push(row);
      }
      this.tiles = newTiles;
    },

    /**
     * Hydrates the store with data received from the backend.
     * @param boardState The full board state object from the API.
     */
    hydrateBoard(boardState: BoardState) {
      this.width = boardState.width;
      this.height = boardState.height;
      this.tiles = boardState.tiles;
    },
  },
});
```

#### **2.3. Vue Konva Component (`BoardCanvas.vue`)**

This component uses `Vue Konva` to render the board based on the Pinia store's state.

```vue
<template>
    <v-stage :config="stageConfig" class="board-canvas-container">
        <v-layer>
            <template v-for="(row, rowIndex) in boardStore.tiles" :key="rowIndex">
                <v-rect
                    v-for="(tile, colIndex) in row"
                    :key="tile.id"
                    :config="getTileConfig(tile)"
                />
            </template>
        </v-layer>
    </v-stage>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useBoardStore } from '@/stores/board';
import { Tile, TileType } from '@/types/board';
import { RectConfig } from 'konva/lib/shapes/Rect';

const boardStore = useBoardStore();
const TILE_SIZE = 40; // Size in pixels for each tile

// Konva stage configuration, dynamically sized
const stageConfig = computed(() => ({
  width: boardStore.width * TILE_SIZE,
  height: boardStore.height * TILE_SIZE,
}));

/**
 * Determines the visual properties (fill color, stroke) for a Konva rectangle based on the tile's data.
 * @param tile The tile object from the Pinia store.
 */
const getTileConfig = (tile: Tile): RectConfig => {
  const isWall = tile.type === TileType.Wall;
  return {
    x: tile.x * TILE_SIZE,
    y: tile.y * TILE_SIZE,
    width: TILE_SIZE,
    height: TILE_SIZE,
    fill: isWall ? '#222' : '#888',
    stroke: isWall ? '#333' : '#666',
    strokeWidth: 1,
  };
};

onMounted(() => {
  // If the store is empty, initialize a default board.
  // In a full application, this would be handled by Inertia props from the backend.
  if (boardStore.tiles.length === 0) {
    boardStore.initializeBoard(22, 28);
  }
});
</script>

<style scoped>
/* Scoped styles for the component */
</style>
```

### **3. Backend Implementation**

The backend is responsible for data persistence and serving the initial state to the frontend via Inertia.

#### **3.1. Database Migration**

A single table with a `JSON` column is the most flexible approach, as it can store the entire 2D grid of tiles and their future layers without requiring schema changes.

```php
// database/migrations/YYYY_MM_DD_create_boards_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The game master
            $table->string('name');
            $table->integer('width');
            $table->integer('height');
            $table->json('tiles'); // Stores the 2D array of tile data
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
```

#### **3.2. Eloquent Model**

The model handles the `JSON` casting, making the `tiles` attribute available as a PHP array.

```php
// app/Models/Board.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'width',
        'height',
        'tiles',
        'user_id',
    ];

    protected $casts = [
        'tiles' => 'array',
    ];
}
```

#### **3.3. Inertia Controller**

The controller creates the initial board state on a `store` request and passes the board data to the Vue component on a `show` request.

```php
// app/Http/Controllers/BoardController.php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BoardController extends Controller
{
    /**
     * Stores a new, empty board.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'width' => 'required|integer|min:1|max:50',
            'height' => 'required|integer|min:1|max:50',
        ]);

        // Generate the default 2D grid of 'wall' tiles.
        $tiles = [];
        for ($y = 0; $y < $validated['height']; $y++) {
            $row = [];
            for ($x = 0; $x < $validated['width']; $x++) {
                $row[] = [
                    'id' => "{$x}_{$y}",
                    'x' => $x,
                    'y' => $y,
                    'type' => 'wall',
                ];
            }
            $tiles[] = $row;
        }

        $board = auth()->user()->boards()->create([
            'name' => $validated['name'],
            'width' => $validated['width'],
            'height' => $validated['height'],
            'tiles' => $tiles,
        ]);

        return redirect()->route('boards.show', $board->id);
    }

    /**
     * Displays a specific board.
     */
    public function show(Board $board)
    {
        // Inertia will automatically pass this as props to the Vue component.
        return Inertia::render('Boards/Show', [
            'board' => $board->only('id', 'name', 'width', 'height', 'tiles'),
        ]);
    }
}
```

### **4. User Workflow**

1.  A logged-in game master navigates to a "Create New Board" page.
2.  They enter a board name and dimensions (defaults to 22x28).
3.  The form is submitted to the `BoardController@store` endpoint.
4.  Laravel generates the initial `tiles` JSON data and saves the board to the database.
5.  Inertia redirects to the `BoardController@show` endpoint for the newly created board.
6.  The `Show.vue` component receives the `board` data as props.
7.  The component uses `useBoardStore().hydrateBoard(props.board)` to populate the Pinia store.
8.  The `BoardCanvas.vue` component, which is a child of `Show.vue`, reads the state from Pinia and renders the grid of non-interactive "wall" tiles using Konva.js.