import { TILE_SIZE } from './grid';

export function tileFromPointer(evt: any, tileSize: number = TILE_SIZE): { x: number; y: number } {
    const stage = evt.target?.getStage?.();
    const pos = stage?.getPointerPosition?.();
    const x = Math.floor((pos?.x ?? 0) / tileSize);
    const y = Math.floor((pos?.y ?? 0) / tileSize);
    return { x, y };
}

export function pointerPx(evt: any): { x: number; y: number } {
    const stage = evt.target?.getStage?.();
    const pos = stage?.getPointerPosition?.();
    return { x: Math.floor(pos?.x ?? 0), y: Math.floor(pos?.y ?? 0) };
}