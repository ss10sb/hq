<script setup lang="ts">
import {
    BoardDimension,
    computeSelectionRect,
    getTileConfig,
    ICON_SIZE,
    iconFor,
    pointerPx,
    preloadAllIcons,
    TILE_SIZE,
    tileFromPointer,
} from '@/lib/board';
import { getHeroAt, isHero } from '@/lib/board/game';
import { Colors, Colors500, heroColorById } from '@/lib/game/colors';
import { badgeCircleConfig, badgeGroupConfig, badgeTextConfig, makeBadge, SearchBadge, SearchBadgeType } from '@/lib/game/searchBadges';
import { useBoardStore } from '@/stores/board';
import { useGameStore } from '@/stores/game';
import {
    Board,
    BoardTool,
    Element,
    ElementType,
    Fixture,
    Monster,
    Tile,
    TileType,
    Trap,
    TrapStatus,
    TrapType
} from '@/types/board';
import { Game } from '@/types/game';
import { Hero, Zargon } from '@/types/hero';
import type { RectConfig } from 'konva/lib/shapes/Rect';
import { computed, onMounted, Ref, ref } from 'vue';

const boardStore = useBoardStore();
const gameStore = useGameStore();

type HeroMovedPayload = { heroes: Hero[]; heroId: number; heroName: string; steps: number };

type HeroMovingPayload = { heroId: number; steps: number };

type MonsterMovedPayload = { monsterName: string; monsterDisplayId: string; steps: number };

const emit = defineEmits<{
    (e: 'toggle-visibility', elementId: string): void;
    (e: 'open-door', elementId: string): void;
    (e: 'hero-moving', payload: HeroMovingPayload): void;
    (e: 'hero-moved', payload: HeroMovedPayload): void;
    (e: 'monster-moved', payload: MonsterMovedPayload): void;
    (e: 'trap-triggered', payload: { heroId: number; heroName: string; trapId: string; trapName: string }): void;
    (e: 'select-tile', payload: { x: number; y: number }): void;
    (e: 'tiles-revealed', payload: { x: number; y: number }[]): void;
    (e: 'elements-changed', payload: { elements: Element[] }): void;
    (
        e: 'tiles-changed',
        payload: {
            tiles: Tile[][];
            changes?: { x: number; y: number; tile: Tile }[];
            fixtureMeta?: Record<string, { type: any; label: string } | null>;
        },
    ): void;
    (e: 'monster-selected', payload: { elementId: string }): void;
}>();

const props = withDefaults(
    defineProps<{
        board?: Board;
        game?: Game;
        canEdit?: boolean;
        monsters?: Monster[];
        traps?: Trap[];
        fixtures?: Fixture[];
        selectedTiles?: { x: number; y: number; color?: string }[];
        playerSelectMode?: boolean;
        canMove?: boolean; // whether the local player can move their character now
        selectedMonsterId?: string | null; // currently selected monster (for visual highlight)
    }>(),
    {
        selectedTiles: () => [],
        playerSelectMode: false,
        canMove: false,
        selectedMonsterId: null,
    },
);

// GM-only search badge tool
const gmSearchBadgeTool = defineModel<{
    active: boolean;
    type: SearchBadgeType;
}>('gmSearchBadgeTool');

const searchBadges = ref<SearchBadge[]>([]);

// Konva stage configuration, dynamically sized
const stageConfig = computed(() => ({
    width: boardStore.width * TILE_SIZE,
    height: boardStore.height * TILE_SIZE,
}));

// Tool mode
const isDrawFloor = computed(() => boardStore.currentTool === BoardTool.DrawFloor);
const isDrawWalls = computed(() => boardStore.currentTool === BoardTool.DrawWalls);
const isAddFixture = computed(() => boardStore.currentTool === BoardTool.AddFixture);
const isToggleVisibility = computed(() => boardStore.currentTool === BoardTool.ToggleVisibility);
const isOpenDoor = computed(() => boardStore.currentTool === BoardTool.OpenDoor);
const isRevealRoom = computed(() => boardStore.currentTool === BoardTool.RevealRoom);
const isRevealCorridor = computed(() => boardStore.currentTool === BoardTool.RevealCorridor);
const isRevealTile = computed(() => boardStore.currentTool === BoardTool.RevealTile);
const isRemoveElement = computed(() => boardStore.currentTool === BoardTool.RemoveElement);
const isMoveMonster = computed(() => boardStore.currentTool === BoardTool.MoveMonster);
const isMoveElement = computed(() => boardStore.currentTool === BoardTool.MoveElement);
const isPaintMode = computed(() => isDrawFloor.value || isDrawWalls.value || isAddFixture.value);

const isSelectionMode = computed(() => isPaintMode.value);

const isElementMode = computed(() => {
    return [
        BoardTool.AddMonster,
        BoardTool.AddDoor,
        BoardTool.AddSecretDoor,
        BoardTool.AddTrap,
        BoardTool.AddTreasure,
        BoardTool.AddPlayerStart,
        BoardTool.AddPlayerExit,
    ].includes(boardStore.currentTool);
});

const isGameMaster = computed(() => boardStore.canEdit);

const noToolSelected = computed(() => boardStore.currentTool === BoardTool.None);

// Selection state (in tile coordinates)
const isDragging: Ref<boolean> = ref(false);
const isPointerDown: Ref<boolean> = ref(false); // pressed but not necessarily dragging
const dragStart = ref<{ x: number; y: number } | null>(null);
const dragCurrent = ref<{ x: number; y: number } | null>(null);

// Movement state (players): click hero to start, move mouse to preview path, click to commit
const movingHeroId = ref<number | null>(null);
const moveStart = ref<{ x: number; y: number } | null>(null);
const movePath = ref<{ x: number; y: number }[]>([]);
// When starting movement with a single click, ignore the first mouseup so users don't need to hold
const ignoreNextMouseUp = ref<boolean>(false);

// Movement state (GM monsters): similar to hero movement but with different traversal rules
const movingMonsterId = ref<string | null>(null);
const monsterMoveStart = ref<{ x: number; y: number } | null>(null);
const monsterMovePath = ref<{ x: number; y: number }[]>([]);

// GM move-any-element: select element then click destination to relocate
const selectedElementId = ref<string | null>(null);
// GM move-hero using Move Element tool: select hero then click destination
const selectedHeroId = ref<number | null>(null);
const selectedHeroStart = ref<{ x: number; y: number } | null>(null);

// Hover state for tooltip
const hoveredTile = ref<{ x: number; y: number } | null>(null);
const mousePos = ref<{ x: number; y: number } | null>(null); // in px, relative to stage
// Viewport-level mouse position for global tooltips (not clipped by canvas bounds)
const mouseViewportPos = ref<{ x: number; y: number } | null>(null);

function isTileTraversableForMonster(x: number, y: number): boolean {
    if (y < 0 || y >= boardStore.height || x < 0 || x >= boardStore.width) {
        return false;
    }
    const t = (boardStore.tiles[y] || [])[x] as any;
    if (!t) {
        return false;
    }
    if (getHeroAt(x, y, gameStore)) {
        return false;
    }
    const el = boardStore.getElementAt(x, y) as any;
    if (el) {
        if (el.type === ElementType.Monster) {
            return true; // may traverse other monsters
        }
        // respect traversable flag for other elements (e.g., open doors, player start)
        return el.traversable === true;
    }
    
    // Check if the tile itself is a traversable fixture
    if (t.type === TileType.Fixture && t.traversable === true) {
        return true;
    }
    
    // Floor tiles: GM can move monsters on any floor tile (hidden or revealed)
    if (t.type === TileType.Floor) {
        return true;
    }
    
    return false;
}

function handleMouseDown(evt: any): void {
    // GM search badge placement (local-only)
    if (isGameMaster.value && gmSearchBadgeTool?.value?.active) {
        const nativeEvt = (evt && (evt.evt || evt)) as MouseEvent | undefined;
        const isRightClick = !!nativeEvt && nativeEvt.button === 2;
        const pos = pointerPx(evt);
        if (!pos) {
            return;
        }
        if (isRightClick) {
            // remove nearest within 16px
            let bestIdx = -1;
            let bestD2 = 16 * 16;
            for (let i = 0; i < searchBadges.value.length; i++) {
                const b = searchBadges.value[i];
                const dx = b.x - pos.x;
                const dy = b.y - pos.y;
                const d2 = dx * dx + dy * dy;
                if (d2 <= bestD2) {
                    bestD2 = d2;
                    bestIdx = i;
                }
            }
            if (bestIdx !== -1) {
                const next = [...searchBadges.value];
                next.splice(bestIdx, 1);
                searchBadges.value = next;
            }
            return; // do not propagate to other tools
        }
        // add a badge at pointer location using current config
        const cfg = gmSearchBadgeTool.value!;
        const badge = makeBadge(pos.x, pos.y, cfg.type);
        searchBadges.value = [...searchBadges.value, badge];
        return; // do not run other handlers when placing badges
    }
    const { x, y } = tileFromPointer(evt);

    // Player select mode: clicking sets selection (available to all roles)
    if (props.playerSelectMode) {
        emit('select-tile', { x, y });
        return;
    }

    // GM reveal tools
    if (isGameMaster.value) {
        if (isRevealRoom.value) {
            const affected = boardStore.revealRoomAt(x, y);
            if (affected && affected.length) {
                emit('tiles-revealed', affected);
            }
            return;
        }
        if (isRevealCorridor.value) {
            const result = boardStore.revealCorridorFrom(x, y);
            if (result.tiles && result.tiles.length) {
                emit('tiles-revealed', result.tiles);
            }
            if (result.elementsChanged) {
                emit('elements-changed', { elements: boardStore.elements as any });
            }
            return;
        }
        if (isRevealTile.value) {
            // Reveal a single tile without revealing any hidden elements on it
            const tile = boardStore.tiles[y]?.[x];
            if (tile && (tile as any).visible !== true) {
                boardStore.setTileVisible(x, y, true);
                emit('tiles-revealed', [{ x, y }]);
            }
            return;
        }
    }

    // GM: Move Monster tool - click a monster to start path movement
    if (isGameMaster.value && isMoveMonster.value) {
        const el = boardStore.getElementAt(x, y);
        if (el && el.type === ElementType.Monster) {
            movingMonsterId.value = el.id;
            monsterMoveStart.value = { x: el.x, y: el.y };
            monsterMovePath.value = [];
            return;
        }
    }

    // GM: Move Any Element tool - select then choose destination (also supports moving heroes)
    if (isGameMaster.value && isMoveElement.value) {
        const el = boardStore.getElementAt(x, y);
        const heroAt = getHeroAt(x, y, gameStore as any);
        if (!selectedElementId.value && selectedHeroId.value == null) {
            if (el) {
                selectedElementId.value = el.id;
                return; // select element to move
            }
            if (heroAt) {
                selectedHeroId.value = heroAt.id as number;
                selectedHeroStart.value = { x: heroAt.x as number, y: heroAt.y as number };
                return; // select hero to move
            }
            return; // nothing selectable at this tile
        }
        // If an element is selected, attempt to move it to clicked tile
        if (selectedElementId.value) {
            const ok = boardStore.moveElement(selectedElementId.value, x, y);
            if (ok) {
                emit('elements-changed', { elements: boardStore.elements as any });
            }
            selectedElementId.value = null;
            // Also clear any pending hero selection
            selectedHeroId.value = null;
            selectedHeroStart.value = null;
            return;
        }
        // If a hero is selected, attempt to move hero directly
        if (selectedHeroId.value != null) {
            const id = selectedHeroId.value as number;
            const ok = gameStore.moveHero(id, x, y, boardStore as any);
            if (ok) {
                const heroName = ((gameStore.heroes as any[])?.find((h: any) => h.id === id)?.name as string | undefined) ?? 'Hero';
                emit('hero-moved', { heroes: gameStore.heroes as any, heroId: id, heroName, steps: 0 });
            }
            selectedHeroId.value = null;
            selectedHeroStart.value = null;
            return;
        }
    }

    // Player movement mode (no tool selected and not GM): click a hero to start/cancel moving
    if (noToolSelected.value && !isGameMaster.value && props.canMove) {
        // If already moving, clicking the original hero tile cancels movement
        if (movingHeroId.value && moveStart.value && x === moveStart.value.x && y === moveStart.value.y) {
            const cancelledId = movingHeroId.value;
            movingHeroId.value = null;
            moveStart.value = null;
            movePath.value = [];
            ignoreNextMouseUp.value = false;
            try {
                if (cancelledId != null) {
                    emit('hero-moving', { heroId: cancelledId, steps: 0 });
                }
            } catch {}
            return;
        }
        const hero = getHeroAt(x, y, gameStore as any);
        // Only allow starting movement for the currently active hero
        if (hero && hero.id === (gameStore as any).currentHeroId) {
            movingHeroId.value = hero.id;
            moveStart.value = { x: hero.x, y: hero.y };
            movePath.value = [];
            // Ignore the mouseup that follows this initial click so we don't force click-and-hold
            ignoreNextMouseUp.value = true;
            try {
                emit('hero-moving', { heroId: hero.id, steps: 0 });
            } catch {}
            return; // Do not start drag selection
        }
    }

    // Start tracking for both paint and element modes (to detect clicks)
    if (noToolSelected.value) {
        // In normal mode, clicking a monster should select it for details panel
        const maybe = boardStore.getElementAt(x, y) as any;
        if (maybe && maybe.type === ElementType.Monster) {
            try {
                emit('monster-selected', { elementId: maybe.id });
            } catch {}
            return;
        }
        return;
    }
    dragStart.value = { x, y };
    dragCurrent.value = { x, y };
    isPointerDown.value = true;
    // Do not mark as dragging yet; wait until cursor moves to a different tile
}

function handleMouseMove(evt: any): void {
    // Always track hover for tooltip
    hoveredTile.value = tileFromPointer(evt);
    mousePos.value = pointerPx(evt);
    // Track viewport coordinates for global tooltip rendering
    const nativeEvt = (evt && (evt.evt || evt)) as MouseEvent | undefined;
    if (nativeEvt && typeof nativeEvt.clientX === 'number' && typeof nativeEvt.clientY === 'number') {
        mouseViewportPos.value = { x: nativeEvt.clientX, y: nativeEvt.clientY };
    }

    // Movement preview update (player hero)
    if (movingHeroId.value && moveStart.value && hoveredTile.value) {
        const next = hoveredTile.value;
        const prev = movePath.value.length > 0 ? movePath.value[movePath.value.length - 1] : moveStart.value;
        // Only act if cursor moved to a different tile
        if (!(next.x === prev.x && next.y === prev.y)) {
            const beforePrev = movePath.value.length >= 2 ? movePath.value[movePath.value.length - 2] : moveStart.value;
            // Allow backtracking by moving onto the previous tile in the path (including start)
            if (beforePrev && beforePrev.x === next.x && beforePrev.y === next.y) {
                movePath.value.pop();
            } else {
                const dx = Math.abs(next.x - prev.x);
                const dy = Math.abs(next.y - prev.y);
                const isAdjacent = dx + dy === 1; // 4-directional only
                if (isAdjacent) {
                    // Only allow stepping onto traversable tiles and not occupied by another hero
                    // Allow traversing through other heroes, but not stopping on them (enforced on commit)
                    // Players use player rules: only revealed floor tiles are traversable
                    if (boardStore.isTileTraversable(next.x, next.y, false)) {
                        movePath.value.push({ x: next.x, y: next.y });
                    }
                }
            }
        }
        // Emit live step count for local preview
        try {
            const steps = movePath.value.length;
            if (movingHeroId.value != null) {
                emit('hero-moving', { heroId: movingHeroId.value, steps });
            }
        } catch {}
        // Do not return; also allow selection rectangle updates for GM tools if needed
    }

    // Movement preview update (GM monsters)
    if (movingMonsterId.value && monsterMoveStart.value && hoveredTile.value) {
        const next = hoveredTile.value;
        const prev = monsterMovePath.value.length > 0 ? monsterMovePath.value[monsterMovePath.value.length - 1] : monsterMoveStart.value;
        if (!(next.x === prev.x && next.y === prev.y)) {
            const beforePrev = monsterMovePath.value.length >= 2 ? monsterMovePath.value[monsterMovePath.value.length - 2] : monsterMoveStart.value;
            if (beforePrev && beforePrev.x === next.x && beforePrev.y === next.y) {
                monsterMovePath.value.pop();
            } else {
                const dx = Math.abs(next.x - prev.x);
                const dy = Math.abs(next.y - prev.y);
                const isAdjacent = dx + dy === 1;
                if (isAdjacent) {
                    if (isTileTraversableForMonster(next.x, next.y)) {
                        monsterMovePath.value.push({ x: next.x, y: next.y });
                    }
                }
            }
        }
    }

    // Update selection rectangle when in selection mode
    if (!isSelectionMode.value) {
        return;
    }
    const { x, y } = tileFromPointer(evt);
    // If pointer is down but not yet dragging, start dragging only when moving to a different tile
    if (isPointerDown.value && dragStart.value) {
        if (x !== dragStart.value.x || y !== dragStart.value.y) {
            isDragging.value = true;
        }
    }
    if (isDragging.value) {
        dragCurrent.value = { x, y };
    }
}

function handleMouseUp(): void {
    // No-op here for context menu; actual prevent is in handleContextMenu
    // Commit movement if in movement mode (player hero)
    if (movingHeroId.value && moveStart.value) {
        // If movement is not permitted (e.g., not player's turn), cancel without applying
        if (!props.canMove) {
            movingHeroId.value = null;
            moveStart.value = null;
            movePath.value = [];
            ignoreNextMouseUp.value = false;
            return;
        }
        // Ignore the mouseup that happens immediately after starting movement
        if (ignoreNextMouseUp.value) {
            ignoreNextMouseUp.value = false;
            return;
        }
        const path = [...movePath.value];
        const end = path.length > 0 ? path[path.length - 1] : null;
        if (end) {
            // Capture hero info before move
            const heroEl = gameStore.heroes.find((h: any) => h.id === movingHeroId.value);
            const heroName = (heroEl as any)?.name ?? 'Hero';
            // Scan path for traps; trigger any armed/hidden traps; handle pit traps by truncating destination
            const toReveal: Array<{ x: number; y: number }> = [];
            const triggeredTraps: Array<{ trapId: string; trapName: string }> = [];
            let pitAt: { x: number; y: number } | null = null;
            const updatedElements = [...boardStore.elements] as any[];
            const updateElementAtIndex = (idx: number, updater: (e: any) => any) => {
                const cur = { ...(updatedElements[idx] as any) };
                updatedElements[idx] = updater(cur);
            };
            for (let i = 0; i < path.length; i++) {
                const step = path[i];
                const elAt = boardStore.getElementAt(step.x, step.y) as any;
                if (elAt && elAt.type === ElementType.Trap) {
                    // Only trigger if not disarmed
                    if (elAt.trapStatus !== TrapStatus.Disarmed) {
                        const idx = updatedElements.findIndex((e: any) => e.id === elAt.id);
                        if (idx !== -1) {
                            updateElementAtIndex(idx, (trap: any) => ({
                                ...trap,
                                hidden: false,
                                trapStatus: TrapStatus.Triggered,
                                color: '#ef4444', // red-500 to indicate danger
                            }));
                        }
                        // Record trap for logging/broadcasting
                        const trapName: string = (elAt.name as string) || (elAt.trapType as string) || 'trap';
                        triggeredTraps.push({ trapId: elAt.id as string, trapName });
                        // Reveal the trap tile to players
                        const t = boardStore.tiles[step.y]?.[step.x] as any;
                        if (t && t.visible !== true) {
                            boardStore.setTileVisible(step.x, step.y, true);
                            toReveal.push({ x: step.x, y: step.y });
                        }
                        // If this is a pit trap, mark the first encountered pit and stop processing further traps
                        if (!pitAt && elAt.trapType === TrapType.Pit) {
                            pitAt = { x: step.x, y: step.y };
                            break; // Stop revealing traps beyond the first pit trap
                        }
                    }
                }
            }
            // Apply any element updates (traps revealed/triggered)
            if (JSON.stringify(updatedElements) !== JSON.stringify(boardStore.elements)) {
                boardStore.elements = updatedElements as any;
                emit('elements-changed', { elements: boardStore.elements as any });
            }
            // Broadcast any fog-of-war reveals caused by trap trigger
            if (toReveal.length > 0) {
                emit('tiles-revealed', toReveal);
            }
            // Emit trap-triggered events for logging
            if (triggeredTraps.length > 0 && movingHeroId.value != null) {
                for (const t of triggeredTraps) {
                    try {
                        emit('trap-triggered', {
                            heroId: movingHeroId.value,
                            heroName,
                            trapId: t.trapId,
                            trapName: t.trapName,
                        });
                    } catch {}
                }
            }
            // Determine tentative final destination (if pit encountered, move back to pit tile)
            let finalIdx = pitAt ? path.findIndex((p) => p.x === pitAt!.x && p.y === pitAt!.y) : path.length - 1;
            let finalDest: { x: number; y: number } | null = pitAt ?? end;
            // Enforce: cannot stop on the same tile as another hero (but traversal allowed earlier)
            while (finalIdx >= 0 && finalDest) {
                const occupant = getHeroAt(finalDest.x, finalDest.y, gameStore as any) as any;
                if (occupant && occupant.id !== movingHeroId.value) {
                    finalIdx -= 1;
                    finalDest = finalIdx >= 0 ? path[finalIdx] : null;
                    continue;
                }
                break;
            }
            if (!finalDest) {
                // No valid stopping tile found; cancel move
                // Reset movement state happens below
            } else {
                const steps = finalIdx + 1; // number of tiles traversed
                const ok = gameStore.moveHero(movingHeroId.value, finalDest.x, finalDest.y, boardStore as any);
                if (ok) {
                    emit('hero-moved', {
                        heroes: gameStore.heroes as any,
                        heroId: movingHeroId.value,
                        heroName,
                        steps,
                    });
                }
            }
        }
        // Reset movement state regardless of success
        const clearedHeroId = movingHeroId.value;
        movingHeroId.value = null;
        moveStart.value = null;
        movePath.value = [];
        try {
            if (clearedHeroId != null) {
                emit('hero-moving', { heroId: clearedHeroId, steps: 0 });
            }
        } catch {}
        return;
    }

    // Commit movement if in monster movement mode (GM)
    if (movingMonsterId.value && monsterMoveStart.value) {
        const path = [...monsterMovePath.value];
        const end = path.length > 0 ? path[path.length - 1] : null;
        if (end) {
            // Get monster details before moving
            const monsterEl = boardStore.elements.find((e: any) => e.id === movingMonsterId.value) as any;
            const steps = path.length;
            
            const ok = boardStore.moveElement(movingMonsterId.value, end.x, end.y);
            if (ok) {
                emit('elements-changed', { elements: boardStore.elements as any });
                
                // Emit monster-moved event for logging (only when steps > 0)
                if (steps > 0 && monsterEl) {
                    const monsterName = monsterEl.name || 'Monster';
                    const monsterDisplayId = monsterEl.displayId || '';
                    emit('monster-moved', { monsterName, monsterDisplayId, steps });
                }
            }
        }
        movingMonsterId.value = null;
        monsterMoveStart.value = null;
        monsterMovePath.value = [];
        return;
    }

    const start = dragStart.value;
    const current = dragCurrent.value;

    if (!start || !current) {
        return;
    }

    const isClick = start.x === current.x && start.y === current.y;

    // GM visibility toggle tool: click to toggle hidden on element present at tile
    if (isClick && isToggleVisibility.value) {
        const el = boardStore.getElementAt(start.x, start.y);
        if (el) {
            emit('toggle-visibility', el.id);
        }
    }

    // GM Open Door tool: click a Door or SecretDoor to open it
    if (isClick && isOpenDoor.value) {
        const el = boardStore.getElementAt(start.x, start.y);
        if (el && (el.type === ElementType.Door || el.type === ElementType.SecretDoor)) {
            emit('open-door', el.id);
        }
    }

    // GM Remove Element tool: click any element to remove it
    if (isClick && isRemoveElement.value) {
        const el = boardStore.getElementAt(start.x, start.y);
        if (el) {
            boardStore.removeElementAt(start.x, start.y);
            emit('elements-changed', { elements: boardStore.elements as any });
        }
    }

    // Paint mode: draw floors, fixtures, or walls (supports click or drag)
    if (isPaintMode.value) {
        const targetType = isDrawFloor.value ? TileType.Floor : isAddFixture.value ? TileType.Fixture : TileType.Wall;
        if (isClick) {
            boardStore.setTileType(start.x, start.y, targetType);
        } else {
            boardStore.setTilesInRect(start.x, start.y, current.x, current.y, targetType);
        }
        // Compute changed coordinates to minimize broadcast payload
        const changes: Array<{ x: number; y: number; tile: Tile }> = [];
        if (isClick) {
            const t = (boardStore.tiles[start.y] as any)[start.x] as Tile;
            changes.push({ x: start.x, y: start.y, tile: t });
        } else {
            const x1 = Math.min(start.x, current.x);
            const x2 = Math.max(start.x, current.x);
            const y1 = Math.min(start.y, current.y);
            const y2 = Math.max(start.y, current.y);
            for (let y = y1; y <= y2; y++) {
                for (let x = x1; x <= x2; x++) {
                    const t = (boardStore.tiles[y] as any)[x] as Tile;
                    changes.push({ x, y, tile: t });
                }
            }
        }
        // Broadcast updated tiles to other clients (send sparse patch as well as full for local application)
        // Fixture labels are now persisted on the tile itself (tile.name), so no extra metadata patch is needed.
        emit('tiles-changed', { tiles: boardStore.tiles as any, changes });
    } else {
        // Element placement mode (single tile toggle)
        if (isClick && isElementMode.value) {
            boardStore.toggleElementAtForCurrentTool(start.x, start.y);
            // Broadcast updated elements to other clients
            emit('elements-changed', { elements: boardStore.elements as any });
        }
    }

    // Reset drag state
    isDragging.value = false;
    isPointerDown.value = false;
    dragStart.value = null;
    dragCurrent.value = null;
}

// Safely prevent browser context menu on Konva stage (Konva passes { evt: MouseEvent })
function handleContextMenu(evt: any): void {
    const nativeEvt: any = evt && (evt.evt || evt);
    if (nativeEvt && typeof nativeEvt.preventDefault === 'function') {
        nativeEvt.preventDefault();
    }
    if (nativeEvt && typeof nativeEvt.stopPropagation === 'function') {
        nativeEvt.stopPropagation();
    }
}

/**
 * Determines the visual properties (fill color, stroke) for a Konva rectangle based on the tile's data.
 * @param tile The tile object from the Pinia store.
 */

const selectionRectConfig = computed<RectConfig>(() => {
    // When dragging, show drag rectangle; otherwise, if hovering in paint mode, show single-tile preview
    if (isDragging.value && dragStart.value && dragCurrent.value) {
        return computeSelectionRect(
            dragStart.value,
            dragCurrent.value,
            { isDrawWalls: isDrawWalls.value, isAddFixture: isAddFixture.value },
            TILE_SIZE,
        );
    }
    if (isSelectionMode.value && !isDragging.value && hoveredTile.value) {
        return computeSelectionRect(
            hoveredTile.value,
            hoveredTile.value,
            { isDrawWalls: isDrawWalls.value, isAddFixture: isAddFixture.value },
            TILE_SIZE,
        );
    }
    return { x: 0, y: 0, width: 0, height: 0 } as RectConfig;
});

const tooltipText = computed(() => {
    if (!hoveredTile.value) {
        return '';
    }
    const { x, y } = hoveredTile.value;
    const tile = boardStore.tiles[y]?.[x] as any;
    // For players, do not show tooltip on unrevealed tiles
    if (!isGameMaster.value) {
        if (!tile || tile.visible !== true) {
            return '';
        }
    }
    // Check for a hero on this tile first
    const hero = getHeroAt(x, y, gameStore as any);
    if (hero) {
        return hero.name;
    }
    const el = boardStore.getElementAt(x, y);
    if (el) {
        // Do not reveal hidden elements to players via tooltip
        if (!isGameMaster.value && (el as any).hidden) {
            return '';
        }
        return el.name || el.type;
    }
    if (tile && tile.type === TileType.Fixture) {
        return boardStore.getFixtureLabel(x, y);
    }
    return '';
});

onMounted(() => {
    if (props.board) {
        boardStore.hydrateBoard(props.board, !!props.canEdit);
    } else {
        // If no board prop is provided, only initialize a default board when editing is explicitly allowed.
        // This prevents gameplay views from creating an always-visible board for players before hydration.
        if (props.canEdit && boardStore.tiles.length === 0) {
            boardStore.initializeBoard(BoardDimension.Width, BoardDimension.Height);
        }
    }

    // Provide catalogs (monsters/traps) from backend to the store
    if (props.monsters || props.traps || props.fixtures) {
        boardStore.setCatalogs(props.monsters as any, props.traps as any, props.fixtures as any);
        // Refresh current monster defaults from catalog if available
        boardStore.setCurrentMonsterSelection(boardStore.currentMonsterType, boardStore.currentMonsterCustomText);
    }
    if (props.game) {
        gameStore.hydrateFromGame(props.game);
    } else {
        // When editing a board (no active game), clear any persisted game data
        gameStore.clearGameData();
    }

    // Preload all icon images so rendering is stable
    preloadAllIcons();
});

// Elements sorted by render priority so heroes draw above player starts
const sortedElements = computed(() => {
    const priority = (t: ElementType): number => {
        if (t === ElementType.PlayerStart) {
            return 0;
        }
        return 10;
    };
    return [...boardStore.elements].sort((a: any, b: any) => priority(a.type) - priority(b.type));
});

// Visible elements based on role and hidden flag and tile visibility
const visibleElements = computed(() => {
    if (isGameMaster.value) {
        return sortedElements.value;
    }
    return sortedElements.value.filter((e: any) => {
        if (e.hidden) {
            return false;
        }
        const t = (boardStore.tiles[e.y] || [])[e.x] as any;
        return !!(t && t.visible === true);
    });
});

// Position of the currently selected monster for visual highlight
const selectedMonsterPos = computed<{ x: number; y: number } | null>(() => {
    const id = props.selectedMonsterId;
    if (!id) {
        return null;
    }
    const el = visibleElements.value.find((e: any) => e.id === id && e.type === ElementType.Monster) as any;
    if (!el) {
        return null;
    }
    return { x: el.x, y: el.y };
});

// Movement indicator positions (emerald/green to distinguish from detail selection)
const movingHeroPos = computed<{ x: number; y: number } | null>(() => {
    if (!movingHeroId.value || !moveStart.value) {
        return null;
    }
    return moveStart.value;
});

const movingMonsterPos = computed<{ x: number; y: number } | null>(() => {
    if (!movingMonsterId.value || !monsterMoveStart.value) {
        return null;
    }
    return monsterMoveStart.value;
});

const selectedElementForMovePos = computed<{ x: number; y: number } | null>(() => {
    // GM Move Element tool: show indicator on selected element
    if (selectedElementId.value) {
        const el = boardStore.elements.find((e: any) => e.id === selectedElementId.value) as any;
        if (el) {
            return { x: el.x, y: el.y };
        }
    }
    // GM Move Element tool: show indicator on selected hero
    if (selectedHeroId.value != null && selectedHeroStart.value) {
        return selectedHeroStart.value;
    }
    return null;
});

// Heroes to render on canvas (exclude Zargon)
const heroesVisible = computed(() => {
    return (gameStore.heroes as Array<Hero | Zargon>).filter((h) => isHero(h)) as Hero[];
});

// For players, tiles containing hidden secret doors should look like walls until revealed
const hiddenSecretDoorCoordSet = computed<Set<string>>(() => {
    if (isGameMaster.value) {
        return new Set<string>();
    }
    const coords = new Set<string>();
    for (const el of boardStore.elements as any) {
        if (el.type === ElementType.SecretDoor && el.hidden) {
            coords.add(`${el.x}:${el.y}`);
        }
    }
    return coords;
});

// For players, tiles containing visible doors should be rendered as Floor
const visibleDoorCoordSet = computed<Set<string>>(() => {
    if (isGameMaster.value) {
        return new Set<string>();
    }
    const coords = new Set<string>();
    for (const el of boardStore.elements as any) {
        if (el.type === ElementType.Door && !el.hidden) {
            coords.add(`${el.x}:${el.y}`);
        }
    }
    return coords;
});

function baseTileConfig(tile: Tile, tileSize: number = TILE_SIZE): RectConfig {
    if (!isGameMaster.value) {
        // If this tile has a hidden secret door, render as a wall for players
        if (hiddenSecretDoorCoordSet.value.has(`${tile.x}:${tile.y}`)) {
            return getTileConfig({ ...tile, type: TileType.Wall } as Tile, tileSize);
        }
        // Fog of war: if tile not visible to players, render as wall
        if ((tile as any).visible !== true) {
            return getTileConfig({ ...tile, type: TileType.Wall } as Tile, tileSize);
        }
        // If this tile has a visible door, render as Floor regardless of underlying tile type
        if (visibleDoorCoordSet.value.has(`${tile.x}:${tile.y}`)) {
            return getTileConfig({ ...tile, type: TileType.Floor } as Tile, tileSize);
        }
    }
    // For the Game Master, show all tiles but visually differentiate unrevealed floors
    const cfg = getTileConfig(tile, tileSize);
    if (isGameMaster.value && tile.type === TileType.Floor && (tile as any).visible !== true) {
        return { ...cfg, fill: '#8A7575' }; // slightly lighter than revealed floor (#888)
    }
    return cfg;
}
</script>

<template>
    <div class="relative inline-block" :style="{ width: stageConfig.width + 'px', height: stageConfig.height + 'px' }" @contextmenu.prevent.stop>
        <!-- Konva stage (tiles + selection overlay) -->
        <v-stage
            :config="stageConfig"
            class="absolute inset-0"
            @mousedown="handleMouseDown"
            @mousemove="handleMouseMove"
            @mouseup="handleMouseUp"
            @mouseleave="handleMouseUp"
            @contextmenu="handleContextMenu"
        >
            <!-- Base tiles layer -->
            <v-layer>
                <template v-for="(row, rowIndex) in boardStore.tiles" :key="rowIndex">
                    <v-rect v-for="tile in row" :key="tile.id" :config="baseTileConfig(tile)" />
                </template>
            </v-layer>

            <!-- Elements layer (deterministic z-order: PlayerStart at bottom, Hero on top) -->
            <v-layer>
                <v-image
                    v-for="el in visibleElements"
                    :key="el.id"
                    :config="{
                        image: iconFor(
                            (el.type === ElementType.Door || el.type === ElementType.SecretDoor) && el.traversable ? 'door_open' : (el.type as any),
                            (el as any).color,
                        ),
                        x: el.x * TILE_SIZE + (TILE_SIZE - ICON_SIZE) / 2,
                        y: el.y * TILE_SIZE + (TILE_SIZE - ICON_SIZE) / 2,
                        width: ICON_SIZE,
                        height: ICON_SIZE,
                        opacity: (el.type === ElementType.PlayerStart ? 0.45 : 1) * (isGameMaster ? (el.hidden ? 0.5 : 1) : 1),
                        listening: false,
                    }"
                />
                <!-- Monster HP badges (top-left of tile) -->
                <template v-for="el in visibleElements" :key="'hp:' + el.id">
                    <template v-if="el.type === ElementType.Monster">
                        <v-text
                            :config="{
                                x: el.x * TILE_SIZE + 2,
                                y: el.y * TILE_SIZE + 2,
                                text: String(el.stats?.currentBodyPoints ?? el.stats?.bodyPoints ?? 0),
                                fontSize: 16,
                                fontStyle: 'bold',
                                fill: Colors.Black,
                                listening: false,
                            }"
                        />
                        <v-text v-if="el.displayId"
                            :config="{
                                x: el.x * TILE_SIZE + (TILE_SIZE - (el.displayId.length * 9)),
                                y: el.y * TILE_SIZE + (TILE_SIZE - 14),
                                text: String(el.displayId),
                                fontSize: 13,
                                fontStyle: 'bold',
                                fill: Colors.Black,
                                listening: false,
                            }"
                        />
                    </template>
                </template>
                <!-- Selected monster highlight -->
                <v-rect
                    v-if="selectedMonsterPos"
                    :config="{
                        x: selectedMonsterPos.x * TILE_SIZE - 1,
                        y: selectedMonsterPos.y * TILE_SIZE - 1,
                        width: TILE_SIZE + 2,
                        height: TILE_SIZE + 2,
                        stroke: Colors500.Blue, // blue-500
                        strokeWidth: 3,
                        listening: false,
                    }"
                />
                <!-- Movement indicators (emerald-500 for movement selection) -->
                <v-rect
                    v-if="movingHeroPos"
                    :config="{
                        x: movingHeroPos.x * TILE_SIZE - 1,
                        y: movingHeroPos.y * TILE_SIZE - 1,
                        width: TILE_SIZE + 2,
                        height: TILE_SIZE + 2,
                        stroke: '#10b981', // emerald-500
                        strokeWidth: 3,
                        listening: false,
                    }"
                />
                <v-rect
                    v-if="movingMonsterPos"
                    :config="{
                        x: movingMonsterPos.x * TILE_SIZE - 1,
                        y: movingMonsterPos.y * TILE_SIZE - 1,
                        width: TILE_SIZE + 2,
                        height: TILE_SIZE + 2,
                        stroke: '#10b981', // emerald-500
                        strokeWidth: 3,
                        listening: false,
                    }"
                />
                <v-rect
                    v-if="selectedElementForMovePos"
                    :config="{
                        x: selectedElementForMovePos.x * TILE_SIZE - 1,
                        y: selectedElementForMovePos.y * TILE_SIZE - 1,
                        width: TILE_SIZE + 2,
                        height: TILE_SIZE + 2,
                        stroke: '#10b981', // emerald-500
                        strokeWidth: 3,
                        listening: false,
                    }"
                />
                <!-- Selected tiles outline -->
                <v-rect
                    v-for="(sel, idx) in props.selectedTiles || []"
                    :key="'sel:' + idx + ':' + sel.x + ':' + sel.y"
                    :config="{
                        x: sel.x * TILE_SIZE + 1,
                        y: sel.y * TILE_SIZE + 1,
                        width: TILE_SIZE - 2,
                        height: TILE_SIZE - 2,
                        stroke: sel.color || '#22c55e',
                        strokeWidth: 3,
                        dash: [6, 4],
                        listening: false,
                    }"
                />
                <!-- Movement path preview -->
                <v-rect
                    v-for="(pt, idx) in movingHeroId ? movePath : movingMonsterId ? monsterMovePath : []"
                    :key="'mv:' + idx + ':' + pt.x + ':' + pt.y"
                    :config="{
                        x: pt.x * TILE_SIZE,
                        y: pt.y * TILE_SIZE,
                        width: TILE_SIZE,
                        height: TILE_SIZE,
                        fill: 'rgba(34,197,94,0.25)', // emerald-500 @ 25%
                        stroke: '#22c55e',
                        strokeWidth: 2,
                        listening: false,
                    }"
                />
                <!-- gm search badges -->
                <template v-if="isGameMaster">
                    <template v-for="b in searchBadges" :key="b.id">
                        <v-group :config="badgeGroupConfig(b)">
                            <v-circle :config="badgeCircleConfig(b)" />
                            <v-text :config="badgeTextConfig(b)" />
                        </v-group>
                    </template>
                </template>
                <!-- heroes -->
                <v-image
                    v-for="h in heroesVisible"
                    :key="'hero:' + h.id"
                    :config="{
                        image: iconFor('hero', heroColorById(h.id)),
                        x: h.x * TILE_SIZE + (TILE_SIZE - ICON_SIZE) / 2,
                        y: h.y * TILE_SIZE + (TILE_SIZE - ICON_SIZE) / 2,
                        width: ICON_SIZE,
                        height: ICON_SIZE,
                        opacity: 1,
                        listening: false,
                    }"
                />
            </v-layer>

            <!-- Selection overlay layer (paint mode hover/drag) -->
            <v-layer v-if="isSelectionMode">
                <v-rect :config="selectionRectConfig" />
            </v-layer>
        </v-stage>

        <!-- Tooltip for elements and fixtures (rendered at document level to avoid clipping) -->
        <teleport to="body">
            <div
                v-if="tooltipText && mouseViewportPos"
                class="fixed z-[9999]"
                :style="{ left: mouseViewportPos.x + 12 + 'px', top: mouseViewportPos.y + 12 + 'px' }"
            >
                <div class="pointer-events-none rounded bg-neutral-900/90 px-2 py-1 text-xs text-white shadow-lg select-none">
                    {{ tooltipText }}
                </div>
            </div>
        </teleport>
    </div>
</template>

<style scoped></style>
