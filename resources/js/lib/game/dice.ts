import { Dice1, Dice2, Dice3, Dice4, Dice5, Dice6, OctagonX, Shield, Skull } from 'lucide-vue-next';

export type DieValue = {
    value: number | string;
    icon: any;
    color?: string;
};

export enum DieType {
    SIX_SIDED = 'six_sided',
    COMBAT = 'combat',
}

export const sixSidedDie: DieValue[] = [
    { value: 1, icon: Dice1 },
    { value: 2, icon: Dice2 },
    { value: 3, icon: Dice3 },
    { value: 4, icon: Dice4 },
    { value: 5, icon: Dice5 },
    { value: 6, icon: Dice6 },
];

export enum CombatDieSide {
    SKULL = 'skull',
    DEFEND = 'defend',
    MONSTER_DEFEND = 'monster_defend',
}

export const combatDie: DieValue[] = [
    { value: CombatDieSide.SKULL, icon: Skull, color: '#FF0000' },
    { value: CombatDieSide.DEFEND, icon: Shield, color: '#00FF00' },
    { value: CombatDieSide.SKULL, icon: Skull, color: '#FF0000' },
    { value: CombatDieSide.DEFEND, icon: Shield, color: '#00FF00' },
    { value: CombatDieSide.SKULL, icon: Skull, color: '#FF0000' },
    { value: CombatDieSide.MONSTER_DEFEND, icon: OctagonX, color: '#0000FF' },
];

function randomIndex(len: number): number {
    if (typeof len !== 'number' || len <= 0) {
        return 0;
    }
    const g: any = (typeof globalThis !== 'undefined' ? globalThis : window) as any;
    const cryptoObj: Crypto | undefined = g?.crypto;
    if (cryptoObj && typeof cryptoObj.getRandomValues === 'function') {
        const maxUint32 = 0x100000000; // 2^32
        const limit = Math.floor(maxUint32 / len) * len;
        const arr = new Uint32Array(1);
        while (true) {
            cryptoObj.getRandomValues(arr);
            const v = arr[0]!;
            if (v < limit) {
                return v % len;
            }
        }
    }
    return Math.floor(Math.random() * len);
}

export function rollDice(type: DieType, count: number): DieValue[] {
    const roll: DieValue[] = [];
    for (let i = 0; i < count; i++) {
        const die = type === DieType.SIX_SIDED ? sixSidedDie : combatDie;
        const idx = randomIndex(die.length);
        const rollValue = die[idx];
        roll.push(rollValue);
    }
    return roll;
}

/**
 * Returns a numeric score for a die face so results can be sorted consistently.
 */
export function scoreDieFace(value: number | string): number {
    if (typeof value === 'number') {
        return value; // numeric face value
    }
    switch (value as any) {
        case CombatDieSide.SKULL:
            return 3;
        case CombatDieSide.DEFEND:
            return 2;
        case CombatDieSide.MONSTER_DEFEND:
            return 1;
        default:
            return 0;
    }
}

/**
 * Expands primitive results (numbers/strings) to DieValue entries including icons/colors.
 */
export function expandPrimitiveResults(diceType: DieType, results: Array<number | string>): DieValue[] {
    const lib = diceType === ('six_sided' as any) ? sixSidedDie : combatDie;
    return results.map((v) => {
        const face = lib.find((d) => d.value === v);
        return face ? { ...face } : { ...(lib[0] as any) };
    }) as DieValue[];
}

/**
 * Returns a new array with DieValue results sorted by score descending.
 */
export function sortDieValues<T extends { value: number | string }>(values: T[]): T[] {
    return [...values].sort((a, b) => scoreDieFace(b.value) - scoreDieFace(a.value));
}
