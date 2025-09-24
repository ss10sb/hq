import { Element, ElementType, TrapStatus } from '@/types/board';

/**
 * Normalize trap icon color based on trap status so triggered traps render as red.
 */
export function applyTrapColorByStatus<T extends Element | any>(el: T): T {
    try {
        if (!el || el.type !== ElementType.Trap) {
            return el;
        }
        const status = (el as any).trapStatus as string | undefined;
        if (status === TrapStatus.Triggered) {
            // red-500
            return { ...el, color: '#ef4444' } as T;
        }
        // For other states, fall back to default icon color (amber) by unsetting explicit overrides
        if ((el as any).color) {
            const { 
                color, // eslint-disable-line @typescript-eslint/no-unused-vars 
                ...rest } = el as any;
            return { ...(rest as any) } as T;
        }
        return el;
    } catch {
        return el;
    }
}
