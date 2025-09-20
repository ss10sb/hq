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
    AddPlayerStart = 'add-player-start',
    AddPlayerExit = 'add-player-exit',
    ToggleVisibility = 'toggle-visibility',
    OpenDoor = 'open-door',
    SelectTile = 'select-tile',
    RevealRoom = 'reveal-room',
    RevealCorridor = 'reveal-corridor',
    RemoveElement = 'remove-element',
    MoveMonster = 'move-monster',
    MoveElement = 'move-element',
}

/**
 * Element types the canvas can render. Mirrors backend where possible.
 * From PHP Enum Board\Elements\Constants\ElementType
 */
export enum ElementType {
    Door = 'door',
    SecretDoor = 'secret_door',
    Monster = 'monster',
    PlayerStart = 'player_start',
    PlayerExit = 'player_exit',
    Trap = 'trap',
    Treasure = 'treasure',
    Custom = 'custom',
    Hero = 'hero',
}

/**
 * An interactive element placed on the board.
 */
import type { Stats } from '@/types/gameplay';

export type Element = {
    id: string; // type:x:y or custom (e.g., hero:{playerId})
    name: string;
    description: string;
    type: ElementType;
    x: number;
    y: number;
    interactive: boolean;
    hidden: boolean;
    traversable: boolean; // Whether players can pass through this element
    // Optional display color (used by heroes for per-player coloring)
    color?: string;
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
     * Whether this tile has been revealed to players (fog of war).
     * GMs see all tiles regardless of this flag.
     */
    visible?: boolean; // Optional for backward compatibility; default false for players
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
    elements: Element[];
}

export type FixtureInfo = {
    type: FixtureType;
    label: string;
};

export interface BoardState extends Board {
    currentTool: BoardTool;
    // Catalogs provided by backend (used to populate defaults/options)
    monstersCatalog: Monster[];
    trapsCatalog: Trap[];
    fixturesCatalog: Fixture[];
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
    canEdit: boolean;
    // Snapshot signature of the last saved/loaded state to detect dirty state
    savedSignature: string;
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
    is_public: boolean;
};

/**
 * From PHP Enum Board\Elements\Monsters\Constants\MonsterType
 */
export enum MonsterType {
    Goblin = 'goblin',
    Skeleton = 'skeleton',
    Zombie = 'zombie',
    Mummy = 'mummy',
    Gargoyle = 'gargoyle',
    DreadWarrior = 'dread-warrior',
    Abomination = 'abomination',
    Orc = 'orc',
    Custom = 'custom',
}

/**
 * From PHP Enum Board\GameBoard\Constants\FixtureType
 */
export enum FixtureType {
    TreasureChest = 'treasure-chest',
    Chair = 'chair',
    Table = 'table',
    Bookcase = 'bookcase',
    Cupboard = 'cupboard',
    AlchemistBench = 'alchemist-bench',
    Altar = 'altar',
    Throne = 'throne',
    Fireplace = 'fireplace',
    TortureRack = 'torture-rack',
    Tomb = 'tomb',
    SorcerersTable = 'sorcerers-table',
    Custom = 'custom',
}

/**
 * From PHP Enum Board\Elements\Traps\Constants\TrapType
 */
export enum TrapType {
    Pit = 'pit',
    Spear = 'spear',
    Block = 'block',
    Custom = 'custom',
}

/**
 * From PHP Enum Board\Elements\Traps\Constants\TrapStatus
 */
export enum TrapStatus {
    Armed = 'armed',
    Disarmed = 'disarmed',
    Triggered = 'triggered',
}

export type Monster = {
    name: string;
    type: MonsterType;
    stats: Stats;
    movementSquares: number;
    custom?: boolean;
};

export type Trap = {
    name: string;
    type: TrapType;
    status: TrapStatus;
    custom?: boolean;
};

export type Fixture = {
    name: string;
    type: FixtureType;
    traversable: boolean;
    custom?: boolean;
}
