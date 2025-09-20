import { Game } from '@/types/game';
import { Board } from '@/types/board';
import { getBoardElementAt, isBoardTileTraversable } from '@/lib/board/board.lib';
import { Hero, Zargon } from '@/types/hero';
import { Dice1, Dice2, Dice3, Dice4, Dice5, Dice6, Shield, Skull, SquareArrowDown, OctagonX } from 'lucide-vue-next';

export function moveGameHero(id: number, toX: number, toY: number, game: Game, board: Board) {
    if (toY < 0 || toY >= board.height || toX < 0 || toX >= board.width) {
        return false;
    }
    const idx = game.heroes.findIndex(h => h.id === id);
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
    const occupant = game.heroes.find(h => (h as any).x === toX && (h as any).y === toY && (h as any).id !== id);
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

function randomIndex(len: number): number {
    if (typeof len !== 'number' || len <= 0) {
        return 0;
    }
    // Prefer cryptographically strong randomness when available
    const g: any = (typeof globalThis !== 'undefined' ? globalThis : window) as any;
    const cryptoObj: Crypto | undefined = g?.crypto;
    if (cryptoObj && typeof cryptoObj.getRandomValues === 'function') {
        // Use rejection sampling to avoid modulo bias
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
    // Fallback to Math.random()
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

export type DieValue = {
    value: number | string;
    icon: any;
    color?: string;
}

export enum DieType {
    SIX_SIDED = 'six_sided',
    COMBAT = 'combat',
}

export const sixSidedDie = [
    {
        value: 1,
        icon: Dice1
    },
    {
        value: 2,
        icon: Dice2
    },
    {
        value: 3,
        icon: Dice3
    },
    {
        value: 4,
        icon: Dice4
    },
    {
        value: 5,
        icon: Dice5
    },
    {
        value: 6,
        icon: Dice6
    }
];

export enum CombatDieSide {
    SKULL = 'skull',
    DEFEND = 'defend',
    MONSTER_DEFEND = 'monster_defend',
}

export const combatDie = [
    {
        value: CombatDieSide.SKULL,
        icon: Skull,
        color: '#FF0000'
    },
    {
        value: CombatDieSide.DEFEND,
        icon: Shield,
        color: '#00FF00'
    },
    {
        value: CombatDieSide.SKULL,
        icon: Skull,
        color: '#FF0000'
    },
    {
        value: CombatDieSide.DEFEND,
        icon: Shield,
        color: '#00FF00'
    },
    {
        value: CombatDieSide.SKULL,
        icon: Skull,
        color: '#FF0000'
    },
    {
        value: CombatDieSide.MONSTER_DEFEND,
        icon: OctagonX,
        color: '#0000FF'
    }
];