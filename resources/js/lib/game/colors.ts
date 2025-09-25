// Deterministic hero color assignment without backend persistence
// Use a fixed palette and map by hero id to ensure consistent color across clients and reloads.
import { SearchBadgeType } from '@/lib/game/searchBadges';

export enum Colors {
    White = '#FFFFFF',
    Black = '#000000',
}

export enum Colors500 {
    Red = '#ef4444',
    Amber = '#f59e0b',
    Emerald = '#10b981',
    Blue = '#3b82f6',
    Green = '#22c55e',
    Purple = '#a855f7',
    Cyan = '#06b6d4',
    Orange = '#f97316',
    Lime = '#84cc16',
    Yellow = '#eab308',
    Pink = '#ec4899',
    Gray = '#6b7280',
}

const PALETTE = [
    Colors500.Red, // red-500
    Colors500.Amber, // amber-500
    Colors500.Emerald, // emerald-500
    Colors500.Blue, // blue-500
    Colors500.Purple, // purple-500
    Colors500.Cyan, // cyan-500
    Colors500.Orange, // orange-500
    Colors500.Lime, // lime-500
    Colors500.Yellow, // yellow-500
    Colors500.Pink, // pink-500
];

export function heroColorById(id: number | null | undefined): string {
    if (typeof id !== 'number' || !Number.isFinite(id)) {
        return Colors500.Blue; // default blue
    }
    // Special case: Zargon uses white to match UI conventions
    if (id === 0) {
        return Colors.White;
    }
    const idx = Math.abs(id) % PALETTE.length;
    return PALETTE[idx];
}

export function heroColor(hero: { id?: number | null } | null | undefined): string {
    return heroColorById((hero as any)?.id ?? null);
}

export function badgeColorForType(type: SearchBadgeType): string {
    switch (type) {
        case 'treasure':
            return Colors500.Blue;
        case 'trap':
            return Colors500.Red;
        case 'secret':
            return Colors500.Purple;
        default:
            return Colors500.Gray;
    }
}
