import type { Board, Element } from '@/types/board';

export function isBoardTileTraversable(x: number, y: number, board: Board) {
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
    // Otherwise, the tile is traversable if the base tile is traversable or the element is explicitly traversable
    if (t.traversable) {
        return true;
    }
    return !!(el && el.traversable === true);
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
    if (!isBoardTileTraversable(toX, toY, board)) {
        return false;
    }
    const elAtDest = getBoardElementAt(toX, toY, board);
    if (elAtDest && elAtDest.id !== id && elAtDest.traversable !== true) {
        return false;
    }
    const current = { ...(board.elements[idx] as Element) } as Element;
    current.x = toX;
    current.y = toY;
    const next = [...board.elements];
    next.splice(idx, 1, current);
    board.elements = next;
    return true;
}
