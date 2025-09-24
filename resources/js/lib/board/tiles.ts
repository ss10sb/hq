import { Tile, TileType } from '@/types/board';
import type { RectConfig } from 'konva/lib/shapes/Rect';
import { TILE_SIZE } from './grid';

export function getTileConfig(tile: Tile, tileSize: number = TILE_SIZE): RectConfig {
    const isWall = tile.type === TileType.Wall;
    const isFixture = tile.type === TileType.Fixture;

    return {
        x: tile.x * tileSize,
        y: tile.y * tileSize,
        width: tileSize,
        height: tileSize,
        fill: isWall ? '#222' : isFixture ? '#5b21b6' : '#888', // fixture: violet-700
        stroke: isWall ? '#333' : isFixture ? '#6d28d9' : '#666', // fixture stroke: violet-600
        strokeWidth: 1,
    } as RectConfig;
}
