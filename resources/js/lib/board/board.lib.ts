import type { Board, Element } from '@/types/board';
import { TileType } from '@/types/board';

export function isBoardTileTraversable(x: number, y: number, board: Board, isGameMaster: boolean = false) {
    if (y < 0 || y >= board.height || x < 0 || x >= board.width) {
        return false;
    }
    const t = board.tiles[y]?.[x];
    if (!t) {
        return false;
    }
    // If there's a non-traversable element on this tile (e.g., monster, hero, closed door), it blocks movement
    const el = getBoardElementAt(x, y, board);
    if (el && el.traversable !== true) {
        return false;
    }
    
    // Check if there's a traversable element present
    if (el && el.traversable === true) {
        return true;
    }
    
    // Check if the tile itself is a traversable fixture
    if (t.type === TileType.Fixture && t.traversable === true) {
        return true;
    }
    
    // Floor tiles are traversable based on role and visibility:
    // - GM can move on any floor tile (hidden or revealed)
    // - Players can only move on revealed floor tiles
    if (t.type === TileType.Floor) {
        if (isGameMaster) {
            return true; // GM can move on any floor tile
        }
        // Players can only move on revealed floor tiles
        return (t as any).visible === true;
    }
    
    return false;
}

export function getBoardElementAt(x: number, y: number, board: Board): Element | undefined {
    const at = board.elements.filter((e) => e.x === x && e.y === y);
    if (at.length <= 1) {
        return at[0];
    }
    // Prefer non-traversable elements (e.g., heroes, monsters) over traversable markers (e.g., PlayerStart)
    const nonTraversable = at.find((e) => e.traversable !== true);
    return nonTraversable ?? at[0];
}

export function moveBoardElement(id: string, toX: number, toY: number, board: Board) {
    if (toY < 0 || toY >= board.height || toX < 0 || toX >= board.width) {
        return false;
    }
    const idx = board.elements.findIndex((e) => e.id === id);
    if (idx === -1) {
        return false;
    }
    // Destination must be traversable (floor or traversable element) and not blocked by non-traversable element
    // moveBoardElement is used by GM tools, so use GM rules (can move to any floor tile)
    if (!isBoardTileTraversable(toX, toY, board, true)) {
        return false;
    }
    const elAtDest = getBoardElementAt(toX, toY, board);
    if (elAtDest && elAtDest.id !== id && elAtDest.traversable !== true) {
        return false;
    }
    const current = { ...(board.elements[idx] as Element) } as Element;
    current.x = toX;
    current.y = toY;
    
    // Sync element visibility with destination tile visibility
    // If moving to a hidden tile, hide the element; if moving to visible tile, reveal it
    const destTile = board.tiles[toY]?.[toX];
    if (destTile) {
        const tileVisible = (destTile as any).visible === true;
        // Update element visibility to match tile visibility
        current.hidden = !tileVisible;
    }
    
    const next = [...board.elements];
    next.splice(idx, 1, current);
    board.elements = next;
    return true;
}
