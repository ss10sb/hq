<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import axios from 'axios';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { Game, Player } from '@/types/game';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { AppPageProps } from '@/types';
import BoardCanvas from '@/components/board/BoardCanvas.vue';
import type { Board, Element as BoardElement, Element, Fixture, Monster, Trap } from '@/types/board';
import { BoardTool, ElementType } from '@/types/board';
import { useBoardStore } from '@/stores/board';
import { useGameStore } from '@/stores/game';
import { useEchoPresence } from '@laravel/echo-vue';
import GamemasterSidebar from '@/components/game/GamemasterSidebar.vue';
import GamePanel from '@/components/game/GamePanel.vue';
import SaveGameController from '@/actions/App/Http/Controllers/Game/SaveGameController';
import { applyTrapColorByStatus } from '@/lib/game/elements';
import {
    broadcastDiceRolled,
    broadcastElementsSync as broadcastElementsSyncUtil,
    broadcastFogOfWarSync as broadcastFogOfWarSyncUtil,
    broadcastGameStateSync as broadcastGameStateSyncUtil,
    broadcastHeroMoved as broadcastHeroMovedUtil,
    broadcastHeroUpdated as broadcastHeroUpdatedUtil,
    broadcastSelectedTileSync as broadcastSelectedTileSyncUtil,
    broadcastTilesSync as broadcastTilesSyncUtil,
    broadcastTrapTriggered,
    type DiceRolledPayload,
    onWhisper,
    type SelectedTilePayload,
    type TrapTriggeredPayload,
    WhisperEvents
} from '@/lib/game/realtime';
import { heroColorById } from '@/lib/game/colors';
import { Hero } from '@/types/hero';
// Scrolling game information panel (log)
import type { DieType, DieValue } from '@/lib/board/game';
import { combatDie, CombatDieSide, rollDice as performRoll, sixSidedDie } from '@/lib/board/game';
import SelectHeroController from '@/actions/App/Http/Controllers/Hero/SelectHeroController';

const page = usePage<AppPageProps>();
const props = defineProps<{
    game: Game;
    board: Board;
    monsters: Monster[];
    traps: Trap[];
    fixtures: Fixture[];
}>();

type LogEntry =
    | { kind: 'text'; text: string }
    | { kind: 'dice'; actor: string; diceType: DieType; count: number; results: DieValue[] };

const gameLog = ref<LogEntry[]>([]);

// Step counters
const stepsByHero = ref<Record<number, number>>({});
const previewStepsByHero = ref<Record<number, number>>({});

// Selected monster details (from board elements)
const selectedMonsterId = ref<string | null>(null);
const selectedMonster = computed<Element | null>(() => {
    const id = selectedMonsterId.value;
    if (!id) {
        return null;
    }
    const el = (boardStoreRef.elements as any[]).find(e => (e as any).id === id) as any;
    if (!el || el.type !== ElementType.Monster) {
        return null;
    }
    return el as Element;
});

// Selected tiles per-hero
const selectedByHero = ref<Record<number, { x: number; y: number } | null>>({});
const selectedTilesDisplay = computed(() => {
    const out: { x: number; y: number; color?: string }[] = [];
    const map = selectedByHero.value || {} as Record<number, { x: number; y: number } | null>;
    for (const [idStr, sel] of Object.entries(map)) {
        const id = Number(idStr);
        if (!Number.isFinite(id)) {
            continue;
        }
        if (sel && typeof sel.x === 'number' && typeof sel.y === 'number') {
            out.push({ x: sel.x, y: sel.y, color: heroColorById(id) });
        }
    }
    return out;
});

const boardStoreRef = useBoardStore();
const gameStore = useGameStore();
const isSelectActive = computed(() => boardStoreRef.currentTool === BoardTool.SelectTile);

const currentUserId = computed<number | null>(() => page.props.auth.user?.id ?? null);
const isGameMaster = computed(() => props.game.gameMasterId === currentUserId.value);

// Whether the local user may move now (they own the active hero and are not GM)
const canMove = computed<boolean>(() => {
    if (isGameMaster.value) {
        return false;
    }
    const owner = gameStore.ownerOfCurrentHero as any;
    return !!(owner && owner.id === currentUserId.value);
});

// Whether the End Turn button should be shown for the local user
const canEndTurn = computed<boolean>(() => {
    const owner = gameStore.ownerOfCurrentHero as any;
    const me = currentUserId.value;
    if (!owner || me == null) {
        return false;
    }
    return owner.id === me; // GM allowed when Zargon is active; players allowed when their hero is active
});

// Element utilities moved to lib/game/elements

// Presence handling (same channel as WaitingRoom)
type PresentUser = { id: number; name: string };
const usersPresent = ref<PresentUser[]>([]);

const { channel } = useEchoPresence<PresentUser>(`game.play.${props.game.id}`, [], () => {
});

onMounted(() => {
    gameStore.hydrateFromGame(props.game);
    // Hydrate board with correct edit permissions based on current user's GM status
    boardStoreRef.hydrateBoard(props.board, isGameMaster.value);

    // Provide catalogs from backend to the board store (used by GM tools for options)
    boardStoreRef.setCatalogs(props.monsters as any, props.traps as any, props.fixtures as any);
    // Refresh current monster defaults from catalog if available
    boardStoreRef.setCurrentMonsterSelection(boardStoreRef.currentMonsterType, boardStoreRef.currentMonsterCustomText);

    const presenceChannel = channel();
    const ensureSelfPresent = () => {
        const myId = currentUserId.value;
        if (myId == null) {
            return;
        }
        if (!usersPresent.value.some(u => u.id === myId)) {
            const selfName = (page.props.auth.user?.name as string | undefined) ?? 'You';
            usersPresent.value.push({ id: myId, name: selfName });
        }
    };

    // Listen for live game state syncs (hero-centric)
    onWhisper(presenceChannel, WhisperEvents.GameStateSync, (data: any) => {
        if (!data) {
            return;
        }
        const incomingHeroes = Array.isArray(data.heroes) ? data.heroes : null;
        const incomingCurrent = typeof data.currentHero === 'number' ? data.currentHero : null;
        if (!incomingHeroes || incomingCurrent === null) {
            return;
        }
        gameStore.setHeroes(incomingHeroes);
        gameStore.setCurrentHero(incomingCurrent);
    });

    // Listen for board elements syncs from any client (players or GM)
    onWhisper(presenceChannel, WhisperEvents.BoardElementsSync, (data: any) => {
        if (!data || !Array.isArray(data.elements)) {
            return;
        }
        // Apply incoming elements to board store (source of truth) and mirror to game store
        const incoming = (data.elements as any[]).map(e => applyTrapColorByStatus(e));
        boardStoreRef.elements = [...incoming] as any;
    });

    // Listen for fog-of-war tile reveals from GM
    onWhisper(presenceChannel, WhisperEvents.FogOfWarSync, (data: any) => {
        if (!data || !Array.isArray(data.tiles)) {
            return;
        }
        // Apply visibility to the local board store
        for (const t of data.tiles as Array<{ x: number; y: number }>) {
            boardStoreRef.setTileVisible(t.x, t.y, true);
        }
    });

    // Listen for board tiles syncs from any client (players or GM)
    onWhisper(presenceChannel, WhisperEvents.BoardTilesSync, (data: any) => {
        // console.log('[BoardTilesSync]', data);
        if (!data) {
            return;
        }
        // Case 1: full matrix replace
        if (Array.isArray((data as any).tiles)) {
            try {
                const incoming = ((data as any).tiles as any[]).map((row: any[]) => [...row]);
                boardStoreRef.tiles = incoming as any;
            } catch {
                // no-op
            }
            // Apply full fixtureMeta if provided
            const fmFull = (data as any).fixtureMeta as Record<string, { type: any; label: string } | null> | undefined;
            if (fmFull && typeof fmFull === 'object') {
                const meta = {} as Record<string, { type: any; label: string }>;
                for (const [k, v] of Object.entries(fmFull)) {
                    if (v != null) {
                        (meta as any)[k] = { ...(v as any) };
                    }
                }
                (boardStoreRef as any).fixtureMeta = meta as any;
            }
            return;
        }
        // Case 2: sparse patch of changes
        const changes = (data as any).changes as Array<{ x: number; y: number; tile: any }> | undefined;
        if (!Array.isArray(changes) || changes.length === 0) {
            // Even if no changes, we might have a pure fixtureMeta patch
            const fmOnly = (data as any).fixtureMeta as Record<string, { type: any; label: string } | null> | undefined;
            if (fmOnly && typeof fmOnly === 'object') {
                const meta = { ...(boardStoreRef as any).fixtureMeta } as any;
                for (const [k, v] of Object.entries(fmOnly)) {
                    if (v == null) {
                        delete meta[k as any];
                    } else {
                        meta[k as any] = { ...(v as any) };
                    }
                }
                (boardStoreRef as any).fixtureMeta = meta;
            }
            return;
        }
        // Apply patches; clone each affected row to maintain reactivity
        const next = (boardStoreRef.tiles as any[]).map((row) => row);
        const touchedRows = new Set<number>();
        for (const c of changes) {
            if (typeof c.x !== 'number' || typeof c.y !== 'number' || !c.tile) {
                continue;
            }
            if (!touchedRows.has(c.y)) {
                next[c.y] = [...(next[c.y] as any[])];
                touchedRows.add(c.y);
            }
            next[c.y][c.x] = { ...(c.tile as any) };
        }
        boardStoreRef.tiles = next as any;
        // Apply fixtureMeta patch if provided
        const fmPatch = (data as any).fixtureMeta as Record<string, { type: any; label: string } | null> | undefined;
        if (fmPatch && typeof fmPatch === 'object') {
            const meta2 = { ...(boardStoreRef as any).fixtureMeta } as any;
            for (const [k, v] of Object.entries(fmPatch)) {
                if (v == null) {
                    delete meta2[k as any];
                } else {
                    meta2[k as any] = { ...(v as any) };
                }
            }
            (boardStoreRef as any).fixtureMeta = meta2;
        }
    });

    // Listen for selected tile syncs from any client (per-hero selection)
    onWhisper<SelectedTilePayload>(presenceChannel, WhisperEvents.SelectedTileSync, (payload) => {
        if (!payload || typeof payload.heroId !== 'number') {
            return;
        }
        // sync the selected tile for this hero
        const heroId = payload.heroId;
        const tile = payload.tile && typeof payload.tile.x === 'number' && typeof payload.tile.y === 'number'
            ? { x: payload.tile.x, y: payload.tile.y }
            : null;
        selectedByHero.value = { ...selectedByHero.value, [heroId]: tile };
    });

    // Ensure everyone has initial heroes/active hero (GM broadcasts on mount)
    if (isGameMaster.value) {
        try {
            broadcastGameStateSync();
        } catch { /* no-op */
        }
    }

    // Listen for hero moved events from any client
    onWhisper(presenceChannel, WhisperEvents.HeroMoved, (payload: {
        heroId: number;
        x: number;
        y: number;
        steps?: number
    }) => {
        if (!payload || typeof payload.heroId !== 'number') {
            return;
        }
        gameStore.moveHero(payload.heroId, payload.x, payload.y, boardStoreRef);
        // Append to game log if steps provided and update step counters
        if (typeof payload.steps === 'number' && payload.steps > 0) {
            const hero = (gameStore.heroes as any[]).find((h: any) => h.id === payload.heroId) as any;
            const name = hero?.name ?? 'Hero';
            const s = payload.steps;
            gameLog.value.unshift({ kind: 'text', text: `${name} moved ${s} ${s === 1 ? 'space' : 'spaces'}` });
            stepsByHero.value = { ...stepsByHero.value, [payload.heroId]: s };
            // Clear any stale preview for this hero
            const { [payload.heroId]: _omit, ...rest } = previewStepsByHero.value;
            previewStepsByHero.value = rest;
        }
        // sync the hero position
    });

    // Listen for hero updated events (stats/inventory/equipment) from any client
    onWhisper(presenceChannel, WhisperEvents.HeroUpdated, (payload: { heroId: number; hero: any }) => {
        if (!payload || typeof payload.heroId !== 'number' || !payload.hero) {
            return;
        }
        const idx = (gameStore.heroes as any[]).findIndex((h: any) => h.id === payload.heroId);
        if (idx === -1) {
            return;
        }
        const existing = (gameStore.heroes as any[])[idx] as any;
        const merged = {
            ...existing,
            ...(payload.hero as any),
            id: existing.id,
            playerId: existing.playerId,
            x: existing.x,
            y: existing.y
        } as any;
        const next = [...(gameStore.heroes as any[])];
        next.splice(idx, 1, merged);
        gameStore.setHeroes(next as any);
    });

    // Listen for trap triggered events from any client
    onWhisper<TrapTriggeredPayload>(presenceChannel, WhisperEvents.TrapTriggered, (payload) => {
        if (!payload) {
            return;
        }
        gameLog.value.unshift({ kind: 'text', text: `${payload.heroName} triggered a ${payload.trapName}` });
    });

    // Listen for dice rolls from any client
    onWhisper<DiceRolledPayload>(presenceChannel, WhisperEvents.DiceRolled, (payload) => {
        if (!payload) {
            return;
        }
        const { actorName, diceType, count, results } = payload as any;
        const expanded = (results as Array<number | string>).map((v) => {
            if (diceType === ('six_sided' as any)) {
                const face = sixSidedDie.find(d => d.value === v);
                return face ? { ...face } : { ...(sixSidedDie[0] as any) };
            } else {
                const face = combatDie.find(d => d.value === v);
                return face ? { ...face } : { ...(combatDie[0] as any) };
            }
        });
        // Sort them in the same order as local
        const scoreFor = (v: number | string): number => {
            if (typeof v === 'number') {
                return v;
            }
            switch (v as any) {
                case CombatDieSide.SKULL:
                    return 3;
                case CombatDieSide.DEFEND:
                    return 2;
                case CombatDieSide.MONSTER_DEFEND:
                    return 1;
                default:
                    return 0;
            }
        };
        const sorted = [...expanded].sort((a, b) => scoreFor(b.value) - scoreFor(a.value));
        gameLog.value.unshift({ kind: 'dice', actor: actorName, diceType, count, results: sorted } as any);
    });

    presenceChannel.here((users: PresentUser[]) => {
        usersPresent.value = [...users];
        ensureSelfPresent();
    });

    presenceChannel.joining(async (user: PresentUser) => {
        if (!usersPresent.value.find(p => p.id === user.id)) {
            usersPresent.value.push(user);
        }
        ensureSelfPresent();
        // update hero/player status for all players and gm, ensure hero placed on board if not GM
    });

    presenceChannel.leaving((user: PresentUser) => {
        const myId = currentUserId.value;
        if (myId != null && user.id === myId) {
            // Ignore self-leave blips
            return;
        }
        const index = usersPresent.value.findIndex(p => p.id === user.id);
        if (index !== -1) {
            usersPresent.value.splice(index, 1);
        }
    });

    // --- Auto-save placeholder (GM only) ---
    if (isGameMaster.value) {
        // Save every 30 seconds by default
        const intervalMs = 30000;
        // Store on window scoped symbol to allow teardown below
        ;(window as any).__hqSaveInterval = window.setInterval(async () => {
            try {
                const payload = {
                    elements: boardStoreRef.elements,
                    heroes: gameStore.heroes,
                    currentHeroId: gameStore.currentHeroId,
                    tiles: boardStoreRef.tiles
                } as any;
                const url = SaveGameController(props.game.id).url;
                await axios.put(url, payload, { withCredentials: true });
                // console.debug('[auto-save] game saved');
            } catch (e) {
                console.warn('[auto-save] failed', e);
            }
        }, intervalMs);
    }
});

onUnmounted(() => {
    const handle = (window as any).__hqSaveInterval;
    if (handle) {
        window.clearInterval(handle);
        delete (window as any).__hqSaveInterval;
    }
});

// --- Players ordering & active selection (GM tools) ---
const orderedPlayers = ref<Player[]>([...props.game.players]);

// Lightweight refresh of game players from the server (used by GM when new players join)
const reloadingPlayers = ref(false);

async function refreshGamePlayers(): Promise<void> {
    if (reloadingPlayers.value) {
        return;
    }
    reloadingPlayers.value = true;
    await new Promise<void>((resolve) => {
        router.reload({
            only: ['game'],
            preserveScroll: true,
            onSuccess: (pageData) => {
                try {
                    const updatedGame = (pageData.props as any).game as Game;
                    if (updatedGame?.players) {
                        orderedPlayers.value = [...updatedGame.players];
                    }
                } finally {
                    reloadingPlayers.value = false;
                    resolve();
                }
            },
            onError: () => {
                reloadingPlayers.value = false;
                resolve();
            },
            onFinish: () => {
                // safety net
                reloadingPlayers.value = false;
            }
        });
    });
}

// Lightweight refresh of game elements from the server (used by GM when new players join)
const reloadingElements = ref(false);

async function refreshGameElements(): Promise<void> {
    if (reloadingElements.value) {
        return;
    }
    reloadingElements.value = true;
    await new Promise<void>((resolve) => {
        router.reload({
            only: ['game'],
            preserveScroll: true,
            onSuccess: (pageData) => {
                try {
                    const updatedGame = (pageData.props as any).game as Game;
                    // apply any updates to the board elements, etc
                } finally {
                    reloadingElements.value = false;
                    resolve();
                }
            },
            onError: () => {
                reloadingElements.value = false;
                resolve();
            },
            onFinish: () => {
                // safety net
                reloadingElements.value = false;
            }
        });
    });
}

// Keep local state in sync if server sends updated props
watch(
    () => props.game,
    (g) => {
        if (!g) {
            return;
        }
        gameStore.hydrateFromGame(g);
    },
    { deep: true }
);

function broadcastGameStateSync(): void {
    try {
        const ch = channel();
        broadcastGameStateSyncUtil(ch, gameStore.currentHeroId, gameStore.heroes as any);
    } catch {
        // no-op
    }
}

function moveUp(index: number): void {
    // Only the Game Master can reorder heroes
    if (!isGameMaster.value) {
        return;
    }
    const list = [...gameStore.heroes];
    if (index <= 0 || index >= list.length) {
        return;
    }
    const tmp = list[index - 1];
    list[index - 1] = list[index];
    list[index] = tmp as any;
    gameStore.setHeroes(list as any);
    // Broadcast the new order and current active hero id
    broadcastGameStateSync();
}

function moveDown(index: number): void {
    // Only the Game Master can reorder heroes
    if (!isGameMaster.value) {
        return;
    }
    const list = [...gameStore.heroes];
    if (index < 0 || index >= list.length - 1) {
        return;
    }
    const tmp = list[index + 1];
    list[index + 1] = list[index];
    list[index] = tmp as any;
    gameStore.setHeroes(list as any);
    // Broadcast the new order and current active hero id
    broadcastGameStateSync();
}

function setActive(heroId: number): void {
    // Set the active hero to the selected hero id
    if (typeof heroId !== 'number') {
        return;
    }
    gameStore.setCurrentHero(heroId);
    if (isGameMaster.value) {
        broadcastGameStateSync();
    }
}

function updateBody(payload: { heroId: number | null; value: number }): void {
    const heroId = payload?.heroId ?? null;
    const value = payload?.value;
    if (heroId == null || typeof value !== 'number') {
        return;
    }
    const hero = (gameStore.heroes as any[]).find((h) => h.id === heroId);
    if (!hero) {
        return;
    }
    const currentUser = currentUserId.value;
    const isOwner = currentUser != null && hero.playerId === currentUser;
    if (!isGameMaster.value && !isOwner) {
        // Not authorized to edit this hero's body points
        return;
    }
    const max = Number(hero?.stats?.bodyPoints ?? 0);
    const nextVal = Math.max(0, Math.min(max, value));
    // Update immutably
    const idx = (gameStore.heroes as any[]).findIndex((h) => h.id === heroId);
    if (idx === -1) {
        return;
    }
    const updated = {
        ...(gameStore.heroes[idx] as any),
        stats: { ...(hero.stats || {}), currentBodyPoints: nextVal }
    } as any;
    const next = [...(gameStore.heroes as any[])];
    next.splice(idx, 1, updated);
    gameStore.setHeroes(next as any);
    // Broadcast updated heroes/current hero to all clients
    if (isGameMaster.value || isOwner) {
        try {
            broadcastGameStateSync();
        } catch {
        }
    }
}

function assignHero(payload: { heroId: number; playerId: number }): void {
    if (!payload || typeof payload.heroId !== 'number' || typeof payload.playerId !== 'number') {
        return;
    }
    if (!isGameMaster.value) {
        return; // only GM can reassign
    }
    const idx = (gameStore.heroes as any[]).findIndex((h: any) => h.id === payload.heroId);
    if (idx === -1) {
        return;
    }
    const existing = (gameStore.heroes as any[])[idx] as any;
    if (existing.playerId === payload.playerId) {
        return; // no change
    }
    const updated = { ...existing, playerId: payload.playerId } as any;
    const next = [...(gameStore.heroes as any[])];
    next.splice(idx, 1, updated);
    gameStore.setHeroes(next as any);
    try {
        broadcastGameStateSync();
    } catch {}
}

async function saveHero(payload: { heroId: number; hero: any }): Promise<void> {
    if (!payload || typeof payload.heroId !== 'number' || !payload.hero) {
        return;
    }
    const heroId = payload.heroId;
    const idx = (gameStore.heroes as any[]).findIndex((h: any) => h.id === heroId);
    if (idx === -1) {
        return;
    }
    const existing = (gameStore.heroes as any[])[idx] as any;
    const me = currentUserId.value;
    const isOwner = me != null && existing.playerId === me;
    if (!isGameMaster.value && !isOwner) {
        // Not authorized
        return;
    }
    // Clean inventory/equipment like Select page does
    const incoming = payload.hero as any;
    const cleaned = {
        ...incoming,
        inventory: Array.isArray(incoming.inventory) ? incoming.inventory.filter((it: any) => it && it.name && String(it.name).trim().length > 0) : [],
        equipment: Array.isArray(incoming.equipment) ? incoming.equipment.filter((eq: any) => eq && eq.name && String(eq.name).trim().length > 0) : []
    } as any;
    try {
        const url = (SelectHeroController as any).update(heroId).url as string;
        await axios.put(url, cleaned, { withCredentials: true });
    } catch (e) {
        // Even if the response redirects, the update likely succeeded; proceed with local/broadcast updates.
    }
    // Merge into existing hero (preserve id, player, position)
    const merged = {
        ...existing,
        ...cleaned,
        id: existing.id,
        playerId: existing.playerId,
        x: existing.x,
        y: existing.y
    } as any;
    const next = [...(gameStore.heroes as any[])];
    next.splice(idx, 1, merged);
    gameStore.setHeroes(next as any);
    // Broadcast to other clients
    try {
        const ch = channel();
        broadcastHeroUpdatedUtil(ch, { heroId, hero: merged } as any);
    } catch {
    }
}

// --- Actions (dice + turn) ---
function rollDice(payload: { type: DieType; count: number }): void {
    if (!payload || (payload.type !== 'six_sided' as any && payload.type !== 'combat' as any)) {
        return;
    }
    const type = payload.type as DieType;
    const count = Math.max(1, Math.min(10, Number(payload.count) || 1));
    // Compute results using shared lib
    const results = performRoll(type, count);
    // Sort results by value for easier scanning
    const scoreFor = (v: number | string): number => {
        if (typeof v === 'number') {
            return v; // numeric face value
        }
        // combat faces ordering
        switch (v as any) {
            case CombatDieSide.SKULL:
                return 3;
            case CombatDieSide.DEFEND:
                return 2;
            case CombatDieSide.MONSTER_DEFEND:
                return 1;
            default:
                return 0;
        }
    };
    const sorted = [...results].sort((a, b) => scoreFor(b.value) - scoreFor(a.value));
    const actor = (page.props.auth.user?.name as string | undefined) ?? 'Someone';
    // Log locally with icons/colors
    gameLog.value.unshift({ kind: 'dice', actor, diceType: type, count, results: sorted });
    // Broadcast to other clients with primitive values
    try {
        const ch = channel();
        const primitive = sorted.map(r => r.value);
        const packet: DiceRolledPayload = { actorName: actor, diceType: type, count, results: primitive } as any;
        broadcastDiceRolled(ch, packet);
    } catch {
    }
}

function endTurn(): void {
    // Advance to the next hero in order (wrap to start) and broadcast to all clients
    const list = [...(gameStore.heroes as any[])];
    if (list.length === 0) {
        return;
    }
    const curId = gameStore.currentHeroId as number;
    const idx = list.findIndex((h: any) => h?.id === curId);
    const nextIdx = idx === -1 ? 0 : (idx + 1) % list.length;
    const nextId = (list[nextIdx] as any)?.id as number | undefined;
    if (typeof nextId !== 'number') {
        return;
    }
    gameStore.setCurrentHero(nextId);
    // Broadcast new active hero so all clients update
    try {
        broadcastGameStateSync();
    } catch {
    }
}

function toggleSelect(): void {
    const store = useBoardStore();
    const wasActive = store.currentTool === BoardTool.SelectTile;
    if (wasActive) {
        // Turning off: switch back to no tool and clear my selection (broadcast removal)
        store.setTool(BoardTool.None);
        clearMySelection();
    } else {
        // Turning on: switch to SelectTile tool
        store.setTool(BoardTool.SelectTile);
    }
}

function chooseSelectionHeroId(): number | null {
    const me = currentUserId.value;
    const activeId = gameStore.currentHeroId;
    const active = (gameStore.heroes as any[]).find(h => (h as any).id === activeId) as any;
    // If GM, use Zargon's id (0) so selection color is Zargon's color
    if (isGameMaster.value) {
        return 0;
    }
    // If the active hero is mine, use it
    if (me != null && active && active.playerId === me) {
        return activeId;
    }
    // Otherwise, find my first available hero
    const mine = (gameStore.heroes as any[]).find(h => (h as any).playerId === me) as any;
    if (mine && typeof mine.id === 'number') {
        return mine.id as number;
    }
    // Otherwise, no selection hero id
    return null;
}

function clearMySelection(): void {
    const heroId = chooseSelectionHeroId();
    if (heroId == null) {
        return;
    }
    selectedByHero.value = { ...selectedByHero.value, [heroId]: null };
    try {
        const ch = channel();
        broadcastSelectedTileSyncUtil(ch, { heroId, tile: null, color: heroColorById(heroId) });
    } catch {
    }
}

function onSelectTile(payload: { x: number; y: number }): void {
    // when a hero/player selects a tile, mark it as selected and broadcast the selection to all clients
    const heroId = chooseSelectionHeroId();
    if (heroId == null) {
        return;
    }
    const current = (selectedByHero.value || {})[heroId];
    const isSame = !!current && current.x === payload.x && current.y === payload.y;
    const next = isSame ? null : { x: payload.x, y: payload.y };
    selectedByHero.value = { ...selectedByHero.value, [heroId]: next };
    try {
        const ch = channel();
        const color = heroColorById(heroId);
        const packet: SelectedTilePayload = { heroId, tile: next, color };
        broadcastSelectedTileSyncUtil(ch, packet);
    } catch {
    }
}

function onHeroMoving(payload: { heroId: number; steps: number }): void {
    if (!payload || typeof payload.heroId !== 'number') {
        return;
    }
    // Update live preview steps for the local mover
    previewStepsByHero.value = { ...previewStepsByHero.value, [payload.heroId]: Math.max(0, payload.steps) };
}

function onHeroMoved(payload: { heroes: Hero[]; heroId: number; heroName: string; steps: number }): void {
    // Extract hero numeric id and new position from payload
    const heroId = payload.heroId;
    const movedEl = payload.heroes.find((e: any) => e.id === heroId) as any;
    gameStore.moveHero(heroId, movedEl.x, movedEl.y, boardStoreRef);
    try {
        const ch = channel();
        broadcastHeroMovedUtil(ch, { heroId: heroId, x: movedEl.x, y: movedEl.y, steps: payload.steps });
    } catch {
    }
    // Update local step counters
    stepsByHero.value = { ...stepsByHero.value, [heroId]: Math.max(0, payload.steps) };
    const { [heroId]: _omit, ...rest } = previewStepsByHero.value;
    previewStepsByHero.value = rest;
    // Log movement in game information panel
    const spaces = payload.steps;
    const msg = `${payload.heroName} moved ${spaces} ${spaces === 1 ? 'space' : 'spaces'}`;
    gameLog.value.unshift({ kind: 'text', text: msg } as any);
}

function onTrapTriggered(payload: TrapTriggeredPayload): void {
    if (!payload || typeof payload.heroName !== 'string' || typeof payload.trapName !== 'string') {
        return;
    }
    gameLog.value.unshift({ kind: 'text', text: `${payload.heroName} triggered a ${payload.trapName}` });
    // Broadcast to other clients
    try {
        const ch = channel();
        broadcastTrapTriggered(ch, payload);
    } catch {
    }
}

function toggleVisibility(elementId: string): void {
    if (!isGameMaster.value) {
        return;
    }
    const list = (boardStoreRef.elements as any[]) || [];
    const idx = list.findIndex((e: any) => e.id === elementId);
    if (idx === -1) {
        return;
    }
    const el = { ...(list[idx] as any) };
    el.hidden = !el.hidden;
    const next = [...list];
    next.splice(idx, 1, el);
    boardStoreRef.elements = next as any;
    try {
        const ch = channel();
        const elements = (next as any[]).map(e => applyTrapColorByStatus(e));
        broadcastElementsSyncUtil(ch, elements as any);
    } catch {
    }
}


function onMonsterSelected(payload: { elementId: string }): void {
    const id = payload?.elementId;
    if (typeof id !== 'string') {
        return;
    }
    selectedMonsterId.value = id;
}

function closeMonster(): void {
    selectedMonsterId.value = null;
}

function updateMonsterBody(payload: { elementId: string; value: number }): void {
    if (!isGameMaster.value) {
        // Only GM can edit monster HP
        return;
    }
    const id = payload?.elementId;
    const value = payload?.value as number;
    if (typeof id !== 'string' || typeof value !== 'number') {
        return;
    }
    const list = (boardStoreRef.elements as any[]) || [];
    const idx = list.findIndex((e: any) => e.id === id);
    if (idx === -1) {
        return;
    }
    const el = { ...(list[idx] as any) } as any;
    if (el.type !== ElementType.Monster) {
        return;
    }
    const max = Number(el?.stats?.bodyPoints ?? 0);
    const nextVal = Math.max(0, Math.min(max, value));
    el.stats = { ...(el.stats || {}), currentBodyPoints: nextVal };
    const next = [...list];
    next.splice(idx, 1, el);
    boardStoreRef.elements = next as any;
    try {
        const ch = channel();
        const elements = (next as any[]).map(e => applyTrapColorByStatus(e));
        broadcastElementsSyncUtil(ch, elements as any);
    } catch {
    }
}

function openDoor(elementId: string): void {
    if (!isGameMaster.value) {
        return;
    }
    const list = (boardStoreRef.elements as any[]) || [];
    const idx = list.findIndex((e: any) => e.id === elementId);
    if (idx === -1) {
        return;
    }
    const el = { ...(list[idx] as any) };
    // Only act on Door or SecretDoor
    if (el.type !== ElementType.Door && el.type !== ElementType.SecretDoor) {
        return;
    }
    el.traversable = true;
    // Opening a secret door reveals it
    if (el.type === ElementType.SecretDoor) {
        el.hidden = false;
    }
    const next = [...list];
    next.splice(idx, 1, el);
    boardStoreRef.elements = next as any;
    try {
        const ch = channel();
        const elements = (next as any[]).map(e => applyTrapColorByStatus(e));
        broadcastElementsSyncUtil(ch, elements as any);
    } catch {
    }
}

function onElementsChanged(payload: { elements: BoardElement[] }): void {
    // Sync updated elements into board store (source of truth) and mirror to game store, then broadcast
    const list = [...(payload.elements as any[])];
    boardStoreRef.elements = list as any;
    try {
        const ch = channel();
        const elements = (list as any[]).map(e => applyTrapColorByStatus(e));
        broadcastElementsSyncUtil(ch, elements as any);
    } catch {
    }
}

function onTilesRevealed(tiles: Array<{ x: number; y: number }>): void {
    try {
        const ch = channel();
        broadcastFogOfWarSyncUtil(ch, tiles);
    } catch {
        // no-op
    }
}

function onTilesChanged(payload: {
    tiles: any[][];
    changes?: { x: number; y: number; tile: any }[];
    fixtureMeta?: Record<string, { type: any; label: string } | null>
}): void {
    // Replace local tiles with a brand new matrix with cloned rows to ensure reactivity
    boardStoreRef.tiles = (payload.tiles as any[]).map((row: any[]) => [...row]) as any;
    // Apply any fixture metadata patch locally as well
    const fixtureMetaPatch = payload.fixtureMeta || {};
    const meta = (boardStoreRef as any).fixtureMeta || {};
    for (const [key, info] of Object.entries(fixtureMetaPatch)) {
        if (info == null) {
            delete meta[key as any];
        } else {
            meta[key as any] = { ...(info as any) };
        }
    }
    (boardStoreRef as any).fixtureMeta = { ...meta };
    try {
        const ch = channel();
        const changes = Array.isArray(payload.changes) ? payload.changes : [];
        if (changes.length > 0) {
            // Broadcast compact patch payload
            const out = changes.map((c) => ({ x: c.x, y: c.y, tile: { ...(c.tile as any) } }));
            const fm = payload.fixtureMeta ? { ...(payload.fixtureMeta as any) } : undefined;
            broadcastTilesSyncUtil(ch, fm ? ({ changes: out, fixtureMeta: fm } as any) : ({ changes: out } as any));
        } else {
            // Fallback: broadcast full matrix (less ideal due to payload size)
            const full = (boardStoreRef.tiles as any[]).map((row: any[]) => row.map((t: any) => ({ ...t })));
            const fullMeta = { ...(boardStoreRef as any).fixtureMeta } as any;
            broadcastTilesSyncUtil(ch, { tiles: full, fixtureMeta: fullMeta } as any);
        }
    } catch {
        // no-op
    }
}
</script>

<template>
    <Head title="Play Game" />
    <AppHeaderLayout>
        <GamemasterSidebar v-if="isGameMaster" />
        <!-- Header -->
        <div class="flex flex-row justify-between items-center gap-3 my-2">
            <div class="flex flex-row items-center gap-2">
                <h1 class="text-2xl font-bold">{{ board.name }}</h1>
                <span>{{ board.group }} ({{ board.order }})</span>
            </div>
            <h2 class="text-lg font-bold">Join Key: {{ game.joinKey }}</h2>
        </div>

        <!-- Main layout: left+ (board), right (game tools) -->
        <div class="flex flex-col md:flex-row gap-4">
            <!-- Board Canvas -->
            <section class="">
                <div
                    class="overflow-auto rounded border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-2">
                    <BoardCanvas
                        :board="board"
                        :can-edit="isGameMaster"
                        :selected-tiles="selectedTilesDisplay"
                        :player-select-mode="(isSelectActive) as any"
                        :can-move="(canMove) as any"
                        :selected-monster-id="selectedMonsterId"
                        @select-tile="onSelectTile"
                        @toggle-visibility="toggleVisibility"
                        @open-door="openDoor"
                        @hero-moving="onHeroMoving"
                        @hero-moved="onHeroMoved"
                        @trap-triggered="onTrapTriggered"
                        @tiles-revealed="onTilesRevealed"
                        @elements-changed="onElementsChanged"
                        @tiles-changed="onTilesChanged"
                        @monster-selected="onMonsterSelected"
                    />
                </div>
            </section>

            <!-- Right gameplay tools sidebar -->
            <GamePanel
                :players="orderedPlayers"
                :heroes="gameStore.heroes"
                :active-hero-id="gameStore.currentHeroId"
                :is-game-master="isGameMaster"
                :present-ids="usersPresent.map(u => u.id)"
                :current-user-id="currentUserId"
                :is-select-active="(isSelectActive) as any"
                :steps-by-hero="stepsByHero"
                :preview-steps-by-hero="previewStepsByHero"
                :can-end-turn="(canEndTurn) as any"
                :selected-monster="selectedMonster"
                :game-log="gameLog"
                @move-up="moveUp"
                @move-down="moveDown"
                @update-body="updateBody"
                @set-active="setActive"
                @roll-dice="rollDice"
                @end-turn="endTurn"
                @toggle-select="toggleSelect"
                @save-hero="saveHero"
                @assign-hero="assignHero"
                @monster-update-body="updateMonsterBody"
                @monster-close="closeMonster"
            />
        </div>

    </AppHeaderLayout>
</template>

<style scoped>
</style>