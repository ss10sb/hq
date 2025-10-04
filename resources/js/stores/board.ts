import {
    BoardDimension,
    defaultElementFlags,
    labelForFixtureType,
    labelForMonsterType,
    labelForTrapType,
    rectBounds,
    statsForMonsterType,
    toolToElementType
} from '@/lib/board';
import { getBoardElementAt, isBoardTileTraversable, moveBoardElement } from '@/lib/board/board.lib';
import {
    Board,
    BoardGroup,
    BoardState,
    BoardTool,
    Element,
    ElementType,
    Fixture,
    FixtureInfo,
    FixtureType,
    Monster,
    MonsterType,
    Tile,
    TileType,
    Trap,
    TrapStatus,
    TrapType
} from '@/types/board';
import type { Stats } from '@/types/gameplay';
import { defineStore } from 'pinia';
import { generateDisplayId, registerDisplayId } from '@/lib/game/elements';

export const useBoardStore = defineStore('board', {
    state: (): BoardState => ({
        id: 0,
        name: 'New Board',
        group: BoardGroup.Core,
        order: 1,
        width: BoardDimension.Width, // Default width
        height: BoardDimension.Height, // Default height
        tiles: [], // An empty 2D array initially
        elements: [],
        currentTool: BoardTool.None,
        // Catalogs from backend
        monstersCatalog: [] as unknown as Monster[],
        trapsCatalog: [] as unknown as Trap[],
        fixturesCatalog: [] as unknown as Fixture[],
        // Fixture selection & metadata
        currentFixtureType: FixtureType.TreasureChest,
        currentFixtureCustomText: '',
        fixtureMeta: {},
        // Trap selection
        currentTrapType: TrapType.Pit,
        currentTrapCustomText: '',
        // Monster selection & stats
        currentMonsterType: MonsterType.Goblin,
        currentMonsterCustomText: '',
        currentMonsterStats: statsForMonsterType(MonsterType.Goblin) as Stats,
        canEdit: false,
        savedSignature: '',
    }),

    getters: {
        currentSignature(): string {
            // Only include fields that represent the board's persisted state
            const payload = {
                id: this.id,
                name: this.name,
                group: this.group,
                order: this.order,
                width: this.width,
                height: this.height,
                tiles: this.tiles,
                elements: this.elements,
            };
            return JSON.stringify(payload);
        },
        isDirty(): boolean {
            return this.savedSignature !== this.currentSignature;
        },
    },

    actions: {
        // --- Fixture subtype selection ---
        setCurrentFixtureSelection(type: FixtureType, customText?: string): void {
            this.currentFixtureType = type;
            this.currentFixtureCustomText = customText ?? this.currentFixtureCustomText;
        },

        // --- Trap subtype selection ---
        setCurrentTrapSelection(type: TrapType, customText?: string): void {
            this.currentTrapType = type;
            this.currentTrapCustomText = customText ?? this.currentTrapCustomText;
        },

        // Catalogs setup
        setCatalogs(monsters: Monster[] = [], traps: Trap[] = [], fixtures: Fixture[] = []): void {
            this.monstersCatalog = monsters ?? ([] as any);
            this.trapsCatalog = traps ?? ([] as any);
            this.fixturesCatalog = fixtures ?? ([] as any);
        },

        // --- Monster subtype selection & stats ---
        setCurrentMonsterSelection(type: MonsterType, customText?: string, statsOverride?: Partial<Stats>): void {
            this.currentMonsterType = type;
            this.currentMonsterCustomText = customText ?? this.currentMonsterCustomText;
            const fromCatalog = (this.monstersCatalog ?? []).find((m: any) => m?.type === type);
            const defaults = (fromCatalog?.stats as Stats | undefined) ?? statsForMonsterType(type);
            this.currentMonsterStats = {
                ...defaults,
                ...(statsOverride ?? {}),
                currentBodyPoints: statsOverride?.currentBodyPoints ?? defaults.currentBodyPoints,
            } as Stats;
        },

        /**
         * Initializes a new board with all tiles set to 'wall' type.
         * @param width The number of columns.
         * @param height The number of rows.
         */
        initializeBoard(width: number, height: number) {
            this.width = width;
            this.height = height;
            const newTiles: Tile[][] = [];
            this.fixtureMeta = {};
            for (let y = 0; y < height; y++) {
                const row: Tile[] = [];
                for (let x = 0; x < width; x++) {
                    row.push({
                        id: `${x}:${y}`,
                        x,
                        y,
                        type: TileType.Wall,
                        visible: false,
                        interactive: false,
                        traversable: false,
                    } as any);
                }
                newTiles.push(row);
            }
            this.tiles = newTiles;
            this.canEdit = true;
            // Consider a freshly initialized board as clean (not dirty)
            this.savedSignature = this.currentSignature;
        },

        /**
         * Hydrates the store with data received from the backend.
         * @param board The full board state object from the API.
         * @param canEdit
         */
        hydrateBoard(board: Board, canEdit: boolean) {
            this.canEdit = canEdit;
            if (!canEdit) {
                this.currentTool = BoardTool.None;
            }
            this.id = board.id;
            this.name = board.name;
            this.group = board.group;
            this.order = board.order;
            this.width = board.width;
            this.height = board.height;
            // Normalize tiles to ensure interactive/traversable are present
            this.tiles = board.tiles.map((row) =>
                row.map((tile) => {
                    const t = { ...tile } as Tile;
                    // Default visibility: players see nothing unless revealed; keep as provided if present
                    if (typeof (t as any).visible === 'undefined' || (t as any).visible === null) {
                        (t as any).visible = false;
                    }
                    if (typeof (t as any).interactive === 'undefined' || typeof (t as any).traversable === 'undefined') {
                        if (t.type === TileType.Wall) {
                            (t as any).interactive = false;
                            (t as any).traversable = false;
                        } else if (t.type === TileType.Floor) {
                            (t as any).interactive = false;
                            (t as any).traversable = true;
                        } else if (t.type === TileType.Fixture) {
                            (t as any).interactive = true;
                            (t as any).traversable = false;
                        }
                    }
                    return t;
                }),
            );

            // Elements: hydrate from backend payload if present; normalize flags and defaults
            const incomingElements = (board as any).elements as Element[] | undefined;
            this.elements = (incomingElements ?? []).map((raw: any) => {
                const type = raw.type as ElementType;
                const flags = defaultElementFlags(type);
                // Resolve element display name with catalog-aware logic
                let resolvedName = raw.name as string | undefined;
                if (!resolvedName) {
                    if (type === ElementType.Trap) {
                        resolvedName = labelForTrapType(raw.trapType ?? this.currentTrapType, raw.customText ?? this.currentTrapCustomText);
                    } else if (type === ElementType.Monster) {
                        const mType = (raw.monsterType ?? this.currentMonsterType) as MonsterType;
                        const fromCatalog = (this.monstersCatalog ?? []).find((m: any) => m?.type === mType);
                        const isCustom = !!(fromCatalog && fromCatalog.custom === true) || mType === MonsterType.Custom;
                        if (isCustom) {
                            resolvedName = labelForMonsterType(mType, raw.customText ?? this.currentMonsterCustomText);
                        } else {
                            resolvedName =
                                (fromCatalog?.name as string | undefined) ??
                                labelForMonsterType(mType, raw.customText ?? this.currentMonsterCustomText);
                        }
                    } else {
                        resolvedName = String(type);
                    }
                }

                const base: Element = {
                    id: raw.id ?? `${type}:${raw.x}:${raw.y}`,
                    displayId: raw.displayId ?? '', 
                    name: resolvedName,
                    description: raw.description ?? '',
                    type,
                    x: Number(raw.x ?? 0),
                    y: Number(raw.y ?? 0),
                    interactive: typeof raw.interactive === 'boolean' ? raw.interactive : flags.interactive,
                    hidden: typeof raw.hidden === 'boolean' ? raw.hidden : flags.hidden,
                    traversable: typeof raw.traversable === 'boolean' ? raw.traversable : flags.traversable,
                    color: typeof raw.color === 'string' ? raw.color : undefined,
                };

                if (type === ElementType.Trap) {
                    return {
                        ...base,
                        trapType: raw.trapType ?? this.currentTrapType,
                        trapStatus: raw.trapStatus ?? TrapStatus.Armed,
                    } as Element;
                }

                if (type === ElementType.Monster) {
                    // Preserve existing displayId if present, otherwise generate new one
                    let displayId = raw.displayId as string | undefined;
                    if (displayId && typeof displayId === 'string' && displayId.length > 0) {
                        registerDisplayId(displayId);
                    } else {
                        displayId = generateDisplayId();
                    }
                    const monsterType = raw.monsterType ?? this.currentMonsterType;
                    const defaults = statsForMonsterType(monsterType);
                    const stats: Stats = {
                        ...defaults,
                        ...(raw.stats ?? {}),
                        currentBodyPoints: raw.stats?.currentBodyPoints ?? defaults.currentBodyPoints,
                    } as Stats;
                    return {
                        ...base,
                        displayId,
                        monsterType,
                        stats,
                    } as Element;
                }

                return base;
            });

            // Reset fixture metadata on hydrate (until persisted via backend)
            this.fixtureMeta = {};
            // Mark current state as clean relative to loaded data
            this.savedSignature = this.currentSignature;
        },

        markSaved(): void {
            this.savedSignature = this.currentSignature;
        },

        setTool(tool: BoardTool): void {
            if (this.currentTool === tool) {
                this.currentTool = BoardTool.None;
                return;
            }
            this.currentTool = tool;
        },

        setTileType(x: number, y: number, type: TileType): void {
            if (y < 0 || y >= this.height || x < 0 || x >= this.width) {
                return;
            }
            const tile = this.tiles[y][x];
            if (tile) {
                // Limit fixture placement to floor tiles (or leave as fixture)
                if (type === TileType.Fixture && tile.type !== TileType.Floor && tile.type !== TileType.Fixture) {
                    return;
                }

                tile.type = type;
                const key = tile.id;
                // Update interaction defaults based on tile type
                if (type === TileType.Wall) {
                    tile.interactive = false;
                    tile.traversable = false;
                    // clear any previous fixture name/metadata
                    (tile as any).name = undefined;
                    delete this.fixtureMeta[key];
                } else if (type === TileType.Floor) {
                    tile.interactive = false;
                    tile.traversable = true;
                    // clear any previous fixture name/metadata
                    (tile as any).name = undefined;
                    delete this.fixtureMeta[key];
                } else if (type === TileType.Fixture) {
                    tile.interactive = true; // fixtures can be interactive
                    // Prefer traversable from catalog if available, else default false
                    const fxFromCatalog = (this.fixturesCatalog ?? []).find((f: any) => f?.type === this.currentFixtureType);
                    tile.traversable = typeof fxFromCatalog?.traversable === 'boolean' ? fxFromCatalog.traversable : false;
                    // set/update fixture label based on current selection and catalog and store on the tile
                    let label: string;
                    if (this.currentFixtureType === FixtureType.Custom) {
                        label = this.currentFixtureCustomText || labelForFixtureType(this.currentFixtureType, this.currentFixtureCustomText);
                    } else {
                        label =
                            (fxFromCatalog?.name as string | undefined) ??
                            labelForFixtureType(this.currentFixtureType, this.currentFixtureCustomText);
                    }
                    (tile as any).name = label;
                    // legacy cleanup: fixtureMeta no longer used for tooltips
                    delete this.fixtureMeta[key];
                }
            }
        },

        setTilesInRect(x1: number, y1: number, x2: number, y2: number, type: TileType): void {
            const { minX, maxX, minY, maxY } = rectBounds({ x: x1, y: y1 }, { x: x2, y: y2 }, this.width, this.height);

            for (let y = minY; y <= maxY; y++) {
                for (let x = minX; x <= maxX; x++) {
                    const t = this.tiles[y]?.[x];
                    if (!t) {
                        continue;
                    }
                    // Extra safety: when painting fixtures over a region, only allow on Floor/Fixture tiles
                    if (type === TileType.Fixture) {
                        if (t.type !== TileType.Floor && t.type !== TileType.Fixture) {
                            continue;
                        }
                    }
                    this.setTileType(x, y, type);
                }
            }
        },

        // --- Visibility & reveal (fog of war) ---

        setTileVisible(x: number, y: number, visible = true): void {
            const t = this.tiles[y]?.[x] as any;
            if (!t) {
                return;
            }
            t.visible = visible;
        },

        isDoorAt(x: number, y: number): boolean {
            return this.elements.some((e: any) => e.x === x && e.y === y && e.type === ElementType.Door);
        },
        isSecretDoorAt(x: number, y: number): boolean {
            return this.elements.some((e: any) => e.x === x && e.y === y && e.type === ElementType.SecretDoor);
        },

        revealRoomAt(x: number, y: number): { x: number; y: number }[] {
            // Flood fill over floor/fixture tiles bounded by walls; do not cross any doors, but include non-secret doors bordering the room
            const inBounds = (cx: number, cy: number) => cy >= 0 && cy < this.height && cx >= 0 && cx < this.width;
            const key = (cx: number, cy: number) => `${cx}:${cy}`;
            const start = this.tiles[y]?.[x];
            if (!start || start.type === TileType.Wall) {
                return [];
            }
            const q: Array<{ x: number; y: number }> = [{ x, y }];
            const seen = new Set<string>([key(x, y)]);
            const roomTiles: Array<{ x: number; y: number }> = [];
            const affected = new Set<string>();
            while (q.length) {
                const cur = q.shift()!;
                roomTiles.push(cur);
                const neighbors = [
                    { x: cur.x + 1, y: cur.y },
                    { x: cur.x - 1, y: cur.y },
                    { x: cur.x, y: cur.y + 1 },
                    { x: cur.x, y: cur.y - 1 },
                ];
                for (const n of neighbors) {
                    if (!inBounds(n.x, n.y)) {
                        continue;
                    }
                    const k = key(n.x, n.y);
                    if (seen.has(k)) {
                        continue;
                    }
                    const t = this.tiles[n.y]?.[n.x];
                    if (!t) {
                        continue;
                    }
                    // Wall bounds the room (stop)
                    if (t.type === TileType.Wall) {
                        continue;
                    }
                    // If a door is here, include the door tile as visible, but don't flood beyond it
                    if (this.isDoorAt(n.x, n.y)) {
                        const before = (this.tiles[n.y]?.[n.x] as any).visible === true;
                        (this.tiles[n.y]?.[n.x] as any).visible = true; // reveal the door tile
                        if (!before) {
                            affected.add(k);
                        }
                        seen.add(k);
                        continue; // do not enqueue beyond door
                    }
                    // Secret doors are not included automatically
                    if (this.isSecretDoorAt(n.x, n.y)) {
                        continue;
                    }
                    seen.add(k);
                    q.push(n);
                }
            }
            for (const rt of roomTiles) {
                const k = key(rt.x, rt.y);
                const before = (this.tiles[rt.y]?.[rt.x] as any).visible === true;
                (this.tiles[rt.y]?.[rt.x] as any).visible = true;
                if (!before) {
                    affected.add(k);
                }
            }
            return Array.from(affected).map((s) => {
                const [sx, sy] = s.split(':');
                return { x: Number(sx), y: Number(sy) };
            });
        },

        revealCorridorFrom(x: number, y: number): { x: number; y: number }[] {
            const inBounds = (cx: number, cy: number) => cy >= 0 && cy < this.height && cx >= 0 && cx < this.width;
            const isBlocking = (cx: number, cy: number) => {
                const t = this.tiles[cy]?.[cx];
                if (!t) {
                    return true;
                }
                if (t.type === TileType.Wall) {
                    return true;
                }
                // Do not reveal beyond doors (but include non-secret door tile itself)
                if (this.isDoorAt(cx, cy)) {
                    return true;
                }
                if (this.isSecretDoorAt(cx, cy)) {
                    return true;
                }
                return false;
            };

            const affected = new Set<string>();
            const key = (cx: number, cy: number) => `${cx}:${cy}`;
            const revealRay = (dx: number, dy: number) => {
                let cx = x,
                    cy = y;
                // First include the starting tile if not blocking
                if (!isBlocking(cx, cy)) {
                    const before = (this.tiles[cy]?.[cx] as any).visible === true;
                    (this.tiles[cy]?.[cx] as any).visible = true;
                    if (!before) {
                        affected.add(key(cx, cy));
                    }
                }
                // March in a straight line until a wall/secret door bounds visibility
                while (true) {
                    const nx = cx + dx,
                        ny = cy + dy;
                    if (!inBounds(nx, ny)) {
                        break;
                    }
                    if (isBlocking(nx, ny)) {
                        // If it is a regular door, reveal the door tile and door element but stop
                        if (this.isDoorAt(nx, ny)) {
                            const before = (this.tiles[ny]?.[nx] as any).visible === true;
                            (this.tiles[ny]?.[nx] as any).visible = true;
                            if (!before) {
                                affected.add(key(nx, ny));
                            }
                            // Also reveal the door element
                            const doorEl = this.elements.find((e: any) => e.x === nx && e.y === ny && e.type === ElementType.Door);
                            if (doorEl) {
                                (doorEl as any).hidden = false;
                            }
                        }
                        break;
                    }
                    const before = (this.tiles[ny]?.[nx] as any).visible === true;
                    (this.tiles[ny]?.[nx] as any).visible = true;
                    if (!before) {
                        affected.add(key(nx, ny));
                    }
                    cx = nx;
                    cy = ny;
                }
            };

            // Reveal in four cardinal directions from the clicked point
            revealRay(1, 0);
            revealRay(-1, 0);
            revealRay(0, 1);
            revealRay(0, -1);
            return Array.from(affected).map((s) => {
                const [sx, sy] = s.split(':');
                return { x: Number(sx), y: Number(sy) };
            });
        },

        // --- Elements management ---

        getElementAt(x: number, y: number): Element | undefined {
            return getBoardElementAt(x, y, this);
        },

        /**
         * Whether a tile can be moved onto or through.
         * A tile is traversable if the base tile is traversable OR there is an element on it that is traversable.
         * Heroes/monsters are not traversable by default.
         */
        isTileTraversable(x: number, y: number): boolean {
            return isBoardTileTraversable(x, y, this);
        },

        /**
         * Move an existing element (by id) to a new tile if the destination is valid.
         */
        moveElement(id: string, toX: number, toY: number): boolean {
            return moveBoardElement(id, toX, toY, this);
        },

        removeElementAt(x: number, y: number): void {
            this.elements = this.elements.filter((e) => !(e.x === x && e.y === y));
        },

        addOrReplaceElementAt(x: number, y: number, type: ElementType, name?: string, description?: string): void {
            // Placement guard: elements may only be placed on Floor or Fixture tiles
            const tile = this.tiles[y]?.[x];
            if (!tile || (tile.type !== TileType.Floor && tile.type !== TileType.Fixture)) {
                return;
            }

            // Remove any existing element on this tile to keep max 1 per tile
            this.removeElementAt(x, y);
            const flags = defaultElementFlags(type);
            // Determine default label/name for certain element types
            let resolvedName = name as string | undefined;
            if (!resolvedName) {
                if (type === ElementType.Trap) {
                    resolvedName = labelForTrapType(this.currentTrapType, this.currentTrapCustomText);
                } else if (type === ElementType.Monster) {
                    const fromCatalog = (this.monstersCatalog ?? []).find((m: any) => m?.type === this.currentMonsterType);
                    const isCustom = !!(fromCatalog && fromCatalog.custom === true) || this.currentMonsterType === MonsterType.Custom;
                    if (isCustom) {
                        resolvedName = labelForMonsterType(this.currentMonsterType, this.currentMonsterCustomText);
                    } else {
                        resolvedName =
                            (fromCatalog?.name as string | undefined) ?? labelForMonsterType(this.currentMonsterType, this.currentMonsterCustomText);
                    }
                } else {
                    resolvedName = String(type);
                }
            }
            let resolvedDisplayId = '';
            if (type === ElementType.Monster) {
                resolvedDisplayId = generateDisplayId();
            }
            
            const baseEl: Element = {
                id: `${type}:${x}:${y}`,
                displayId: resolvedDisplayId,
                name: resolvedName,
                description: description ?? '',
                type,
                x,
                y,
                interactive: flags.interactive,
                hidden: flags.hidden, // traps default to hidden via flags
                traversable: flags.traversable,
            };

            // Attach trap/monster-specific metadata
            let el: Element = baseEl;
            if (type === ElementType.Trap) {
                el = { ...baseEl, trapType: this.currentTrapType, trapStatus: TrapStatus.Armed } as Element;
            } else if (type === ElementType.Monster) {
                el = { ...baseEl, monsterType: this.currentMonsterType, stats: { ...this.currentMonsterStats } } as Element;
            }

            this.elements.push(el);
        },

        toggleElementAtForCurrentTool(x: number, y: number): void {
            const type = toolToElementType(this.currentTool);
            if (!type) {
                return;
            }
            const existing = this.getElementAt(x, y);
            if (existing && existing.type === type) {
                // Clicking the same type on the same tile removes it (toggle off)
                this.removeElementAt(x, y);
                return;
            }

            // Special rule: Only one PlayerExit allowed on the board at a time
            if (type === ElementType.PlayerExit) {
                const existingExit = this.elements.find((e) => e.type === ElementType.PlayerExit);
                if (existingExit) {
                    // If clicking the same tile as existing exit, treat as toggle off
                    if (existingExit.x === x && existingExit.y === y) {
                        this.removeElementAt(x, y);
                        return;
                    }
                    // Otherwise, remove the previous exit before placing the new one
                    this.removeElementAt(existingExit.x, existingExit.y);
                }
            }

            // Multiple PlayerStart markers are allowed; default behavior already supports this.
            this.addOrReplaceElementAt(x, y, type);
        },

        // --- Fixture metadata queries ---
        getFixtureInfoAt(x: number, y: number): FixtureInfo | undefined {
            const t = this.tiles[y]?.[x];
            if (!t) {
                return undefined;
            }
            return this.fixtureMeta[t.id];
        },

        getFixtureLabel(x: number, y: number): string {
            const t = this.tiles[y]?.[x] as any;
            if (!t) {
                return '';
            }
            // Prefer persisted tile name for fixtures (customizable and saved)
            if (typeof t.name === 'string' && t.name.length > 0) {
                return t.name as string;
            }
            if (t.type === TileType.Fixture) {
                return 'Fixture';
            }
            return '';
        },
    },
});
