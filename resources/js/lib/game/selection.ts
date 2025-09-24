import { heroColorById } from '@/lib/game/colors';
import type { SelectedTilePayload } from '@/lib/game/realtime';

/**
 * Decide which hero id to associate with tile selection for the given user.
 */
export function chooseSelectionHeroId(isGameMaster: boolean, currentUserId: number | null, currentHeroId: number, heroes: any[]): number | null {
    if (isGameMaster) {
        return 0; // Zargon
    }
    const active = heroes.find((h: any) => (h as any).id === currentHeroId) as any;
    if (currentUserId != null && active && active.playerId === currentUserId) {
        return currentHeroId;
    }
    const mine = heroes.find((h: any) => (h as any).playerId === currentUserId) as any;
    if (mine && typeof mine.id === 'number') {
        return mine.id as number;
    }
    return null;
}

export type SimpleTile = { x: number; y: number };

/**
 * Map the per-hero selected tile structure into a display array with colors.
 */
export function toSelectedTilesDisplay(selectedByHero: Record<number, SimpleTile | null>): Array<SimpleTile & { color?: string }> {
    const out: Array<SimpleTile & { color?: string }> = [];
    for (const [idStr, sel] of Object.entries(selectedByHero || ({} as Record<number, SimpleTile | null>))) {
        const id = Number(idStr);
        if (!Number.isFinite(id)) {
            continue;
        }
        if (sel && typeof sel.x === 'number' && typeof sel.y === 'number') {
            out.push({ x: sel.x, y: sel.y, color: heroColorById(id) });
        }
    }
    return out;
}

/**
 * Build the SelectedTilePayload with the proper hero color.
 */
export function buildSelectedTilePacket(heroId: number, tile: SimpleTile | null): SelectedTilePayload {
    return { heroId, tile, color: heroColorById(heroId) } as SelectedTilePayload;
}
