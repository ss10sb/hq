import type { Stats } from '@/types/gameplay';

export enum HeroArchetype {
    Berserker = 'berserker',
    Dwarf = 'dwarf',
    Elf = 'elf',
    Wizard = 'wizard',
    Custom = 'custom',
}

export type NewHero = {
    name: string;
    type: HeroArchetype;
    stats: Stats;
    inventory: InventoryItem[];
    equipment: EquipmentItem[];
};

export interface Character {
    id: number;
    name: string;
    playerId: number;
}

export interface Zargon extends Character {} // eslint-disable-line @typescript-eslint/no-empty-object-type

export interface Hero extends Character {
    type: HeroArchetype;
    stats: Stats;
    inventory: InventoryItem[];
    equipment: EquipmentItem[];
    x: number;
    y: number;
}

export type InventoryItem = {
    name: string;
    description: string;
    quantity: number;
};

export type EquipmentItem = {
    name: string;
    description: string;
    attackDice: number;
    defenseDice: number;
};
