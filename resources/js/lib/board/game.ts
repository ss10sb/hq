import { getBoardElementAt, isBoardTileTraversable } from '@/lib/board/board.lib';
import { Board } from '@/types/board';
import { Game } from '@/types/game';
import { Hero, Zargon } from '@/types/hero';

export function moveGameHero(id: number, toX: number, toY: number, game: Game, board: Board) {
    if (toY < 0 || toY >= board.height || toX < 0 || toX >= board.width) {
        return false;
    }
    const idx = game.heroes.findIndex((h) => h.id === id);
    if (idx === -1) {
        return false;
    }
    // Destination must be traversable (floor or traversable element) and not blocked by non-traversable element
    if (!isBoardTileTraversable(toX, toY, board)) {
        return false;
    }
    const elAtDest = getBoardElementAt(toX, toY, board);
    if (elAtDest && elAtDest.traversable !== true) {
        return false;
    }
    // Disallow ending on another hero's tile
    const occupant = game.heroes.find((h) => (h as any).x === toX && (h as any).y === toY && (h as any).id !== id);
    if (occupant) {
        return false;
    }
    const current = { ...(game.heroes[idx] as Hero) } as Hero;
    current.x = toX;
    current.y = toY;
    const next = [...game.heroes];
    next.splice(idx, 1, current);
    game.heroes = next;
    return true;
}

export function getHeroAt(x: number, y: number, game: Game) {
    for (const hero of game.heroes) {
        if (!isHero(hero)) {
            continue;
        }
        if (hero.x === x && hero.y === y) {
            return hero;
        }
    }
}

export function isHero(hero: Hero | Zargon): hero is Hero {
    return typeof hero.x === 'number' && typeof hero.y === 'number';
}
