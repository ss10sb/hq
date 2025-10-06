import { Tile, TileType } from '@/types/board';
import type { RectConfig } from 'konva/lib/shapes/Rect';
import { TILE_SIZE } from './grid';

export function getTileConfig(tile: Tile, tileSize: number = TILE_SIZE): RectConfig {
    return {
        x: tile.x * tileSize,
        y: tile.y * tileSize,
        width: tileSize,
        height: tileSize,
        fill: tileFillColor(tile), // fixture: violet-700
        stroke: tileStrokeColor(tile), // fixture stroke: violet-600
        strokeWidth: 1,
    } as RectConfig;
}

function tileFillColor(tile: Tile): string {
    if (tile.type === TileType.Wall) {
        return '#222';
    }
    if (tile.type === TileType.Fixture) {
        return tile.traversable ? '#90cdf4' : '#5b21b6';
    }
    return '#888';
}

function tileStrokeColor(tile: Tile): string {
    if (tile.type === TileType.Wall) {
        return '#333';
    }
    if (tile.type === TileType.Fixture) {
        return tile.traversable ? '#63b3ed' : '#6d28d9';
    }
    return '#666';
}
