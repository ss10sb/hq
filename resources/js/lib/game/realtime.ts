import type { Element } from '@/types/board';
import type Echo from 'laravel-echo';

// Central registry of whisper event names used by gameplay.
export const WhisperEvents = {
    GameStateSync: 'gameState.sync',
    BoardElementsSync: 'board.elements.sync',
    BoardElementsPatchSync: 'board.elements.patch',
    BoardTilesSync: 'board.tiles.sync',
    SelectedTileSync: 'board.selected.sync',
    // Future expansion (placeholders)
    FogOfWarSync: 'fog.sync',
    HeroMoved: 'hero.moved',
    HeroUpdated: 'hero.updated',
    MonsterMoved: 'monster.moved',
    MonsterUpdated: 'monster.updated',
    DoorOpened: 'door.opened',
    DoorClosed: 'door.closed',
    TrapTriggered: 'trap.triggered',
    TrapDisarmed: 'trap.disarmed',
    VisibilitySync: 'visibility.sync',
    DiceRolled: 'dice.rolled',
    TurnEnded: 'turn.ended',
} as const;

export type WhisperEventName = (typeof WhisperEvents)[keyof typeof WhisperEvents];

export function broadcastGameStateSync(channel: Echo.Channel, currentHero: number, heroes: any[]): void {
    try {
        channel.whisper(WhisperEvents.GameStateSync, {
            currentHero,
            heroes,
        });
    } catch {
        // no-op
    }
}

export function broadcastElementsSync(channel: Echo.Channel, elements: Element[]): void {
    try {
        channel.whisper(WhisperEvents.BoardElementsSync, { elements });
    } catch {
        // no-op
    }
}

// --- Element Patch Sync (compact updates) ---
export type ElementsPatchV1 = {
    v: 1;
    changes: Array<
        | { op: 'add'; element: Element }
        | { op: 'remove'; id: string }
        | { op: 'update'; id: string; patch: Partial<Element> }
        | { op: 'move'; id: string; x: number; y: number }
        | { op: 'visibility'; id: string; hidden: boolean }
        | { op: 'hp'; id: string; currentBodyPoints: number }
    >;
};

export function broadcastElementsPatchSync(channel: Echo.Channel, payload: ElementsPatchV1): void {
    try {
        channel.whisper(WhisperEvents.BoardElementsPatchSync, payload);
    } catch {
        // no-op
    }
}

export type SelectedTilePayload = { heroId: number; tile: { x: number; y: number } | null; color?: string };

export function broadcastSelectedTileSync(channel: Echo.Channel, payload: SelectedTilePayload): void {
    try {
        console.log('broadcastSelectedTileSync', payload);
        channel.whisper(WhisperEvents.SelectedTileSync, payload);
    } catch {
        // no-op
    }
}

/**
 * Utility to safely listen for known whispers on a presence channel.
 * Unknown events are ignored, helping future-proof handlers.
 */
export function onWhisper<T = any>(channel: Echo.Channel, event: WhisperEventName, handler: (payload: T) => void): void {
    channel.listenForWhisper(event, (data: T) => handler(data));
}

export type FogRevealPayload = { tiles: { x: number; y: number }[] };

export function broadcastFogOfWarSync(channel: Echo.Channel, tiles: { x: number; y: number }[]): void {
    try {
        channel.whisper(WhisperEvents.FogOfWarSync, { tiles } as FogRevealPayload);
    } catch {
        // no-op
    }
}

export type TilesSyncChange = { x: number; y: number; tile: any };
export type TilesFixtureMetaPatch = Record<string, { type: any; label: string } | null>;
export type TilesSyncPayload =
    | { tiles: any[][]; fixtureMeta?: TilesFixtureMetaPatch }
    | { changes: TilesSyncChange[]; fixtureMeta?: TilesFixtureMetaPatch };

export function broadcastTilesSync(channel: Echo.Channel, payload: TilesSyncPayload): void {
    try {
        channel.whisper(WhisperEvents.BoardTilesSync, payload);
    } catch {
        // no-op
    }
}

export type HeroMovedPayload = { heroId: number; x: number; y: number; steps?: number };

export function broadcastHeroMoved(channel: Echo.Channel, payload: HeroMovedPayload): void {
    try {
        channel.whisper(WhisperEvents.HeroMoved, payload);
    } catch {
        // no-op
    }
}

export type HeroUpdatedPayload = { heroId: number; hero: any };

export function broadcastHeroUpdated(channel: Echo.Channel, payload: HeroUpdatedPayload): void {
    try {
        channel.whisper(WhisperEvents.HeroUpdated, payload);
    } catch {
        // no-op
    }
}

// --- Monster movement logging ---
export type MonsterMovedPayload = { monsterName: string; monsterDisplayId?: string; steps: number };

export function broadcastMonsterMoved(channel: Echo.Channel, payload: MonsterMovedPayload): void {
    try {
        channel.whisper(WhisperEvents.MonsterMoved, payload);
    } catch {
        // no-op
    }
}

export type TrapTriggeredPayload = { heroId: number; heroName: string; trapId: string; trapName: string };

export function broadcastTrapTriggered(channel: Echo.Channel, payload: TrapTriggeredPayload): void {
    try {
        channel.whisper(WhisperEvents.TrapTriggered, payload);
    } catch {
        // no-op
    }
}

// --- Dice Rolls ---
import type { DieType } from '@/lib/game/dice';
export type DiceRolledPayload = { actorName: string; diceType: DieType; count: number; results: Array<number | string> };

export function broadcastDiceRolled(channel: Echo.Channel, payload: DiceRolledPayload): void {
    try {
        channel.whisper(WhisperEvents.DiceRolled, payload);
    } catch {
        // no-op
    }
}

export type TurnEndedPayload = { heroName: string };

export function broadcastTurnEnded(channel: Echo.Channel, payload: TurnEndedPayload): void {
    try {
        channel.whisper(WhisperEvents.TurnEnded, payload);
    } catch {
        // no-op
    }
}
