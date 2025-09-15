import type { RectConfig } from 'konva/lib/shapes/Rect';
import { TILE_SIZE } from './grid';

export function computeSelectionRect(
    dragStart: { x: number; y: number } | null,
    dragCurrent: { x: number; y: number } | null,
    options: { isDrawWalls: boolean; isAddFixture: boolean },
    tileSize: number = TILE_SIZE,
): RectConfig {
    if (!dragStart || !dragCurrent) {
        return { x: 0, y: 0, width: 0, height: 0 } as RectConfig;
    }

    const minX = Math.min(dragStart.x, dragCurrent.x) * tileSize;
    const minY = Math.min(dragStart.y, dragCurrent.y) * tileSize;
    const maxX = (Math.max(dragStart.x, dragCurrent.x) + 1) * tileSize;
    const maxY = (Math.max(dragStart.y, dragCurrent.y) + 1) * tileSize;

    // Use different visual indicators depending on the active tool
    // Floor: blue, Walls: red, Fixtures: purple
    const fillColor = options.isDrawWalls
        ? 'rgba(239,68,68,0.15)'
        : options.isAddFixture
            ? 'rgba(168,85,247,0.15)'
            : 'rgba(59,130,246,0.15)';

    const strokeColor = options.isDrawWalls
        ? 'rgba(239,68,68,0.85)'
        : options.isAddFixture
            ? 'rgba(168,85,247,0.85)'
            : 'rgba(59,130,246,0.8)';

    return {
        x: minX,
        y: minY,
        width: maxX - minX,
        height: maxY - minY,
        fill: fillColor,
        stroke: strokeColor,
        strokeWidth: 2,
        dash: [6, 4],
        listening: false,
    } as unknown as RectConfig;
}

/**
 * Calculate clamped rectangle bounds in tile coordinates based on two drag points.
 * Returns minX, maxX, minY, maxY (inclusive) clamped to board dimensions.
 */
export function rectBounds(
    start: { x: number; y: number },
    current: { x: number; y: number },
    boardWidth: number,
    boardHeight: number,
): { minX: number; maxX: number; minY: number; maxY: number } {
    const minX = Math.max(0, Math.min(start.x, current.x));
    const maxX = Math.min(boardWidth - 1, Math.max(start.x, current.x));
    const minY = Math.max(0, Math.min(start.y, current.y));
    const maxY = Math.min(boardHeight - 1, Math.max(start.y, current.y));
    return { minX, maxX, minY, maxY };
}
