/**
 * Runtime definitions for Board-related types and enums.
 *
 * Note: This file must be a real .ts module (not .d.ts) because some
 * values (enums) are used at runtime in components/stores.
 */

/**
 * An enum representing the different types of tiles on the board.
 * This is easily extendable for future features like doors, traps, etc.
 * From PHP Enum TileType
 */
export enum TileType {
    Wall = 'wall', // Non-interactive; default state for an empty board.
    Floor = 'floor', // Interactive; can contain characters, items, etc.
    Fixture = 'fixture',
}

export enum BoardTool {
    None = 'none',
    DrawFloor = 'draw-floor',
    DrawWalls = 'draw-walls',
    AddMonster = 'add-monster',
    AddFixture = 'add-fixture',
    AddDoor = 'add-door',
    AddSecretDoor = 'add-secret-door',
    AddTrap = 'add-trap',
    AddTreasure = 'add-treasure',
}

/**
 * Element types the canvas can render. Mirrors backend where possible.
 * From PHP Enum ElementType
 */
export enum ElementType {
    Door = 'door',
    SecretDoor = 'secret_door',
    Monster = 'monster',
    Trap = 'trap',
    Treasure = 'treasure', // Not yet in backend enum; front-end only for now
}

/**
 * An interactive element placed on the board.
 */
import type { Stats } from '@/types/gameplay';

export type Element = {
    id: string; // use an uuid for this
    name: string;
    description: string;
    type: ElementType;
    x: number;
    y: number;
    interactive: boolean;
    hidden: boolean;
    passthrough: boolean; // Whether players can pass through this element
    // Trap-specific metadata (when type === ElementType.Trap)
    trapType?: TrapType;
    trapStatus?: TrapStatus;
    // Monster-specific metadata (when type === ElementType.Monster)
    monsterType?: MonsterType;
    stats?: Stats;
};

/**
 * The data structure for a single square tile on the board.
 */
export type Tile = {
    id: string; // A unique identifier, e.g., '0:0', '1:2'.
    x: number; // The column index.
    y: number; // The row index.
    type: TileType; // The type of the tile (wall, floor, fixture).
    /**
     * Flags that control interaction and movement on this tile.
     * - Walls: interactive=false, traversable=false
     * - Floors: interactive=false, traversable=true
     * - Fixtures: interactive=true (by default), traversable=false (by default)
     */
    interactive: boolean;
    traversable: boolean;
};

export interface Board {
    id: number;
    name: string;
    group: BoardGroup;
    order: number;
    width: number;
    height: number;
    tiles: Tile[][];
}

export type FixtureInfo = {
    type: FixtureType;
    label: string;
};

export interface BoardState extends Board {
    currentTool: BoardTool;
    elements: Element[];
    // Fixture subtype selection & metadata
    currentFixtureType: FixtureType;
    currentFixtureCustomText: string;
    fixtureMeta: Record<string, FixtureInfo>; // key = tile.id ("x:y")
    // Trap subtype selection
    currentTrapType: TrapType;
    currentTrapCustomText: string;
    // Monster subtype selection & stats
    currentMonsterType: MonsterType;
    currentMonsterCustomText: string;
    currentMonsterStats: Stats;
}

export enum BoardGroup {
    Core = 'core',
    Custom = 'custom',
}

export type BoardGroups = {
    [key: string]: BoardGroup;
};


/**
 * Shape for creating a new board from the New Board form.
 */
export type NewBoard = {
    name: string;
    group: keyof BoardGroups | BoardGroup;
    order: number;
    width: number;
    height: number;
};

/**
 * From PHP Enum MonsterType
 */
export enum MonsterType {
    Goblin = 'goblin',
    Skeleton = 'skeleton',
    Orc = 'orc',
    Custom = 'custom',
}

/**
 * From PHP Enum FixtureType
 */
export enum FixtureType {
    TreasureChest = 'treasure_chest',
    Chair = 'chair',
    Table = 'table',
    Custom = 'custom',
}

/**
 * From PHP Enum TrapType
 */
export enum TrapType {
    Pit = 'pit',
    Spear = 'spear',
    Block = 'block',
    Custom = 'custom',
}

/**
 * From PHP Enum TrapStatus
 */
export enum TrapStatus {
    Armed = 'armed',
    Triggered = 'triggered',
    Disarmed = 'disarmed',
}