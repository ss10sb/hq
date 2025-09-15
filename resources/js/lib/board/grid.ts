// Grid-related constants and helpers shared across board features

// Centralized tile size so canvas, DOM overlays, and gameplay logic use the same value
export const TILE_SIZE = 30;

// Default board dimensions used when initializing a new board
export enum BoardDimension {
    Height = 22,
    Width = 28,
}