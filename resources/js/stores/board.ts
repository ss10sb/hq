import { defineStore } from 'pinia';
import { Board, BoardGroup, BoardState, Tile, TileType, BoardTool, ElementType, Element, FixtureType, FixtureInfo, TrapType, TrapStatus, MonsterType } from '@/types/board';
import type { Stats } from '@/types/gameplay';
import { rectBounds, defaultElementFlags, toolToElementType, BoardDimension, labelForFixtureType, labelForTrapType, labelForMonsterType, statsForMonsterType } from '@/lib/board';

export const useBoardStore = defineStore('board', {
    state: (): BoardState => ({
        id: 0,
        name: 'New Board',
        group: BoardGroup.Core,
        order: 1,
        width: BoardDimension.Width, // Default width
        height: BoardDimension.Height, // Default height
        tiles: [], // An empty 2D array initially
        currentTool: BoardTool.None,
        elements: [],
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
    }),

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

        // --- Monster subtype selection & stats ---
        setCurrentMonsterSelection(type: MonsterType, customText?: string, statsOverride?: Partial<Stats>): void {
            this.currentMonsterType = type;
            this.currentMonsterCustomText = customText ?? this.currentMonsterCustomText;
            const defaults = statsForMonsterType(type);
            this.currentMonsterStats = { ...defaults, ...(statsOverride ?? {}), currentBodyPoints: (statsOverride?.currentBodyPoints ?? defaults.currentBodyPoints) };
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
                        interactive: false,
                        traversable: false,
                    });
                }
                newTiles.push(row);
            }
            this.tiles = newTiles;
        },

        /**
         * Hydrates the store with data received from the backend.
         * @param board The full board state object from the API.
         */
        hydrateBoard(board: Board) {
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
            // Reset fixture metadata on hydrate (until persisted via backend)
            this.fixtureMeta = {};
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
                    // remove any fixture meta if present
                    delete this.fixtureMeta[key];
                } else if (type === TileType.Floor) {
                    tile.interactive = false;
                    tile.traversable = true;
                    // remove any fixture meta if present
                    delete this.fixtureMeta[key];
                } else if (type === TileType.Fixture) {
                    tile.interactive = true; // fixtures can be interactive
                    tile.traversable = false; // fixtures block movement by default
                    // set/update fixture subtype & label based on current selection
                    const label = labelForFixtureType(this.currentFixtureType, this.currentFixtureCustomText);
                    this.fixtureMeta[key] = { type: this.currentFixtureType, label } as FixtureInfo;
                }
            }
        },

        setTilesInRect(x1: number, y1: number, x2: number, y2: number, type: TileType): void {
            const { minX, maxX, minY, maxY } = rectBounds(
                { x: x1, y: y1 },
                { x: x2, y: y2 },
                this.width,
                this.height,
            );

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

        // --- Elements management ---

        getElementAt(x: number, y: number): Element | undefined {
            return this.elements.find((e) => e.x === x && e.y === y);
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
            let resolvedName = name;
            if (!resolvedName) {
                if (type === ElementType.Trap) {
                    resolvedName = labelForTrapType(this.currentTrapType, this.currentTrapCustomText);
                } else if (type === ElementType.Monster) {
                    resolvedName = labelForMonsterType(this.currentMonsterType, this.currentMonsterCustomText);
                } else {
                    resolvedName = String(type);
                }
            }

            const baseEl: Element = {
                id: `${type}:${x}:${y}`,
                name: resolvedName,
                description: description ?? '',
                type,
                x,
                y,
                interactive: flags.interactive,
                hidden: flags.hidden, // traps default to hidden via flags
                passthrough: flags.passthrough,
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
                this.removeElementAt(x, y);
                return;
            }
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
            const info = this.getFixtureInfoAt(x, y);
            return info?.label ?? '';
        },
    },
});
