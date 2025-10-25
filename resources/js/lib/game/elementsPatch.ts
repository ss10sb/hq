import { applyTrapColorByStatus } from '@/lib/game/elements';
import type { ElementsPatchV1 } from '@/lib/game/realtime';
import type { Element } from '@/types/board';

/**
 * Apply a compact elements patch to an elements array, returning a new array instance.
 * - Preserves reactivity by cloning the array and only cloning touched items.
 */
export function applyElementsPatch(current: Element[], payload: ElementsPatchV1): Element[] {
    if (!payload || payload.v !== 1 || !Array.isArray(payload.changes)) {
        return current;
    }
    let next = [...(current as any[])];

    const indexOf = (id: string): number => next.findIndex((e: any) => String(e?.id) === String(id));

    for (const change of payload.changes as any[]) {
        if (!change || typeof change.op !== 'string') {
            continue;
        }
        switch (change.op) {
            case 'add': {
                if (change.element && change.element.id) {
                    // Avoid duplicates: replace if already exists
                    const idx = indexOf(change.element.id);
                    const el = applyTrapColorByStatus(change.element as any);
                    if (idx === -1) {
                        next = [...next, el as any];
                    } else {
                        const cloned = [...next];
                        cloned.splice(idx, 1, el as any);
                        next = cloned as any;
                    }
                }
                break;
            }
            case 'remove': {
                if (typeof change.id === 'string') {
                    next = next.filter((e: any) => e?.id !== change.id) as any;
                }
                break;
            }
            case 'move': {
                if (typeof change.id === 'string' && typeof change.x === 'number' && typeof change.y === 'number') {
                    const idx = indexOf(change.id);
                    if (idx !== -1) {
                        const cur = next[idx] as any;
                        const updated = { ...cur, x: change.x, y: change.y } as any;
                        const cloned = [...next];
                        cloned.splice(idx, 1, applyTrapColorByStatus(updated));
                        next = cloned as any;
                    }
                }
                break;
            }
            case 'visibility': {
                if (typeof change.id === 'string' && typeof change.hidden === 'boolean') {
                    const idx = indexOf(change.id);
                    if (idx !== -1) {
                        const cur = next[idx] as any;
                        const updated = { ...cur, hidden: change.hidden } as any;
                        const cloned = [...next];
                        cloned.splice(idx, 1, applyTrapColorByStatus(updated));
                        next = cloned as any;
                    }
                }
                break;
            }
            case 'hp': {
                if (typeof change.id === 'string' && typeof change.currentBodyPoints === 'number') {
                    const idx = indexOf(change.id);
                    if (idx !== -1) {
                        const cur = next[idx] as any;
                        const stats = { ...(cur.stats || {}), currentBodyPoints: change.currentBodyPoints } as any;
                        const updated = { ...cur, stats } as any;
                        const cloned = [...next];
                        cloned.splice(idx, 1, applyTrapColorByStatus(updated));
                        next = cloned as any;
                    }
                }
                break;
            }
            case 'update': {
                if (typeof change.id === 'string' && change.patch && typeof change.patch === 'object') {
                    const idx = indexOf(change.id);
                    if (idx !== -1) {
                        const cur = next[idx] as any;
                        const updated = { ...cur, ...(change.patch as any) } as any;
                        const cloned = [...next];
                        cloned.splice(idx, 1, applyTrapColorByStatus(updated));
                        next = cloned as any;
                    }
                }
                break;
            }
            default:
                // unknown op; ignore
                break;
        }
    }
    return next as any;
}

/**
 * Compute a minimal patch between two arrays of elements (by id).
 * Currently detects add/remove and shallow updates of selected fields, including position/visibility/hp.
 */
export function diffElements(prev: Element[], next: Element[]): ElementsPatchV1 {
    const changes: ElementsPatchV1['changes'] = [];
    const prevById = new Map<string, any>();
    for (const e of prev as any[]) {
        if (e && typeof e.id === 'string') {
            prevById.set(e.id, e);
        }
    }
    const nextById = new Map<string, any>();
    for (const e of next as any[]) {
        if (e && typeof e.id === 'string') {
            nextById.set(e.id, e);
        }
    }

    // removals
    for (const id of prevById.keys()) {
        if (!nextById.has(id)) {
            changes.push({ op: 'remove', id } as any);
        }
    }

    // additions and updates
    const fieldsToTrack = new Set<string>([
        'x',
        'y',
        'hidden',
        'traversable',
        'type',
        'name',
        'color',
        'displayId',
        'trapStatus',
        'trapType',
    ]);

    for (const [id, nextEl] of nextById.entries()) {
        const prevEl = prevById.get(id);
        if (!prevEl) {
            changes.push({ op: 'add', element: nextEl } as any);
            continue;
        }
        const patch: Record<string, any> = {};
        for (const key of fieldsToTrack) {
            if ((prevEl as any)[key] !== (nextEl as any)[key]) {
                patch[key] = (nextEl as any)[key];
            }
        }
        // stats.currentBodyPoints as special-case
        const prevHP = (prevEl as any)?.stats?.currentBodyPoints;
        const nextHP = (nextEl as any)?.stats?.currentBodyPoints;
        if (prevHP !== nextHP && typeof nextHP === 'number') {
            patch.stats = { ...((nextEl as any).stats || {}), currentBodyPoints: nextHP } as any;
        }
        if (Object.keys(patch).length > 0) {
            // If only x/y changed, prefer the compact 'move' op
            const keys = Object.keys(patch);
            if (keys.length === 2 && 'x' in patch && 'y' in patch) {
                changes.push({ op: 'move', id, x: patch.x as number, y: patch.y as number } as any);
            } else if (keys.length === 1 && 'hidden' in patch) {
                changes.push({ op: 'visibility', id, hidden: !!patch.hidden } as any);
            } else if (keys.length === 1 && patch.stats && typeof patch.stats.currentBodyPoints === 'number') {
                changes.push({ op: 'hp', id, currentBodyPoints: patch.stats.currentBodyPoints } as any);
            } else {
                // generic update with shallow fields
                const shallow = { ...patch } as any;
                if (shallow.stats) {
                    // keep only currentBodyPoints in stats to reduce payload
                    shallow.stats = { currentBodyPoints: shallow.stats.currentBodyPoints } as any;
                }
                changes.push({ op: 'update', id, patch: shallow } as any);
            }
        }
    }

    return { v: 1, changes } as ElementsPatchV1;
}
