import { Colors500 } from '@/lib/game/colors';
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
            return { ...el, color: Colors500.Red } as T;
        }
        // For other states, fall back to default icon color (amber) by unsetting explicit overrides
        if ((el as any).color) {
            const {
                color, // eslint-disable-line @typescript-eslint/no-unused-vars
                ...rest
            } = el as any;
            return { ...(rest as any) } as T;
        }
        return el;
    } catch {
        return el;
    }
}


// Track used display IDs to ensure uniqueness
const usedDisplayIds = new Set<string>();

/**
 * Generates incrementing display IDs using capital letters.
 * Sequence: A-Z, AA-ZZ, AAA-ZZZ, etc.
 * 
 * @returns The next available display ID
 */
export function generateDisplayId(): string {
    let length = 1;
    
    while (true) {
        // Generate all possible IDs of current length
        const maxForLength = Math.pow(26, length);
        
        for (let i = 0; i < maxForLength; i++) {
            const id = numberToAlpha(i, length);
            if (!usedDisplayIds.has(id)) {
                usedDisplayIds.add(id);
                return id;
            }
        }
        
        // Move to next length (A-Z -> AA-ZZ -> AAA-ZZZ, etc.)
        length++;
    }
}

/**
 * Converts a number to alphabetical representation
 * @param num The number to convert (0-based)
 * @param length The desired length of the output string
 */
function numberToAlpha(num: number, length: number): string {
    let result = '';
    let n = num;
    
    for (let i = 0; i < length; i++) {
        const remainder = n % 26;
        result = String.fromCharCode(97 + remainder) + result; // 65 is 'A'
        n = Math.floor(n / 26);
    }
    
    return result;
}

/**
 * Resets the used display IDs tracker.
 * Useful when starting a new game or loading saved state.
 */
export function resetDisplayIds(): void {
    usedDisplayIds.clear();
}

/**
 * Registers an existing display ID as used.
 * Call this when loading saved elements to prevent ID collisions.
 */
export function registerDisplayId(id: string): void {
    if (id && typeof id === 'string') {
        usedDisplayIds.add(id);
    }
}