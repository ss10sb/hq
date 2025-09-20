// Deterministic hero color assignment without backend persistence
// Use a fixed palette and map by hero id to ensure consistent color across clients and reloads.

const PALETTE = [
    '#ef4444', // red-500
    '#f59e0b', // amber-500
    '#10b981', // emerald-500
    '#3b82f6', // blue-500
    '#a855f7', // purple-500
    '#06b6d4', // cyan-500
    '#f97316', // orange-500
    '#84cc16', // lime-500
    '#eab308', // yellow-500
    '#ec4899', // pink-500
];

export function heroColorById(id: number | null | undefined): string {
    if (typeof id !== 'number' || !Number.isFinite(id)) {
        return '#3b82f6'; // default blue
    }
    // Special case: Zargon uses white to match UI conventions
    if (id === 0) {
        return '#ffffff';
    }
    const idx = Math.abs(id) % PALETTE.length;
    return PALETTE[idx];
}

export function heroColor(hero: { id?: number | null } | null | undefined): string {
    return heroColorById((hero as any)?.id ?? null);
}
