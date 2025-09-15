<script setup lang="ts">
import { Ref, computed, onMounted, ref } from 'vue';
import { useBoardStore } from '@/stores/board';
import { Board, BoardTool, TileType } from '@/types/board';
import { BoardDimension } from '@/lib/board';
import type { RectConfig } from 'konva/lib/shapes/Rect';
import { TILE_SIZE, computeSelectionRect, getTileConfig, tileFromPointer, pointerPx } from '@/lib/board';
import { Bomb, DoorClosed, Gem, Key, Skull } from 'lucide';

const boardStore = useBoardStore();

const props = defineProps<{
    board?: Board;
}>();

// Konva stage configuration, dynamically sized
const stageConfig = computed(() => ({
    width: boardStore.width * TILE_SIZE,
    height: boardStore.height * TILE_SIZE,
}));

// Tool mode
const isDrawFloor = computed(() => boardStore.currentTool === BoardTool.DrawFloor);
const isDrawWalls = computed(() => boardStore.currentTool === BoardTool.DrawWalls);
const isAddFixture = computed(() => boardStore.currentTool === BoardTool.AddFixture);
const isPaintMode = computed(() => isDrawFloor.value || isDrawWalls.value || isAddFixture.value);

const isSelectionMode = computed(() => isPaintMode.value);

const isElementMode = computed(() => {
    return [
        BoardTool.AddMonster,
        BoardTool.AddDoor,
        BoardTool.AddSecretDoor,
        BoardTool.AddTrap,
        BoardTool.AddTreasure,
    ].includes(boardStore.currentTool);
});

const noToolSelected = computed(() => boardStore.currentTool === BoardTool.None);

// Lucide SVG rendering for Konva
const ICON_SIZE = 24;

type IconChild = [string, Record<string, any>];
type IconNode = IconChild[]; // Lucide icon nodes are arrays of [tag, attrs]

const iconMap: Record<string, IconNode | undefined> = {
    monster: Skull as unknown as IconNode,
    door: DoorClosed as unknown as IconNode,
    secret_door: Key as unknown as IconNode,
    trap: Bomb as unknown as IconNode,
    treasure: Gem as unknown as IconNode,
};

const iconColor: Record<string, string> = {
    monster: 'oklch(50.5% 0.213 27.518)', // red-700
    door: 'oklch(27.9% 0.077 45.635)', // amber-950
    secret_door: 'oklch(28.3% 0.141 291.089)', // violet-950
    trap: '#f59e0b', // amber-500
    treasure: '#10b981', // emerald-500
};

function iconNodeToSvg(children: IconNode, color: string, size = ICON_SIZE): string {
    const baseAttrs: Record<string, string> = {
        xmlns: 'http://www.w3.org/2000/svg',
        width: String(size),
        height: String(size),
        viewBox: '0 0 24 24',
        fill: 'none',
        stroke: color,
        'stroke-width': '2',
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
    };
    const attrsToString = (a: Record<string, any>) =>
        Object.entries(a)
            .map(([k, v]) => `${k}="${String(v)}"`)
            .join(' ');

    const childStr = (children || [])
        .map(([cTag, cAttrs]) => `<${cTag} ${attrsToString(cAttrs)}></${cTag}>`)
        .join('');

    return `<svg ${attrsToString(baseAttrs)}>${childStr}</svg>`;
}

const imageCache = new Map<string, HTMLImageElement>();
const imagesVersion = ref(0);

function prepareIcon(type: string): void {
    const key = `${type}`;
    if (imageCache.has(key)) {
        return;
    }
    const node = iconMap[type];
    if (!node) {
        return;
    }
    const color = iconColor[type] ?? '#38bdf8';
    const svg = iconNodeToSvg(node, color, ICON_SIZE);
    const img = new Image();
    img.onload = () => {
        imagesVersion.value++;
    };
    img.onerror = () => {
        imageCache.delete(key);
    };
    img.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
    imageCache.set(key, img);
}

function iconFor(type: string): HTMLImageElement | null {
    const img = imageCache.get(type);
    if (img && (img as any).complete && (img as any).naturalWidth > 0) {
        return img;
    }
    return null;
}


// Selection state (in tile coordinates)
const isDragging: Ref<boolean> = ref(false);
const dragStart = ref<{ x: number; y: number } | null>(null);
const dragCurrent = ref<{ x: number; y: number } | null>(null);

// Hover state for tooltip
const hoveredTile = ref<{ x: number; y: number } | null>(null);
const mousePos = ref<{ x: number; y: number } | null>(null); // in px, relative to stage


function handleMouseDown(evt: any): void {
    // Start tracking for both paint and element modes (to detect clicks)
    if (noToolSelected.value) {
        return;
    }
    const { x, y } = tileFromPointer(evt);
    dragStart.value = { x, y };
    dragCurrent.value = { x, y };
    isDragging.value = true;
}

function handleMouseMove(evt: any): void {
    // Always track hover for tooltip
    hoveredTile.value = tileFromPointer(evt);
    mousePos.value = pointerPx(evt);

    // Update selection rectangle when in selection mode and dragging
    if (!isSelectionMode.value || !isDragging.value) {
        return;
    }
    const { x, y } = tileFromPointer(evt);
    dragCurrent.value = { x, y };
}

function handleMouseUp(): void {
    const start = dragStart.value;
    const current = dragCurrent.value;

    if (!start || !current) {
        return;
    }

    const isClick = start.x === current.x && start.y === current.y;

    // Paint mode: draw floors, fixtures, or walls (supports click or drag)
    if (isPaintMode.value) {
        const targetType = isDrawFloor.value ? TileType.Floor : (isAddFixture.value ? TileType.Fixture : TileType.Wall);
        if (isClick) {
            boardStore.setTileType(start.x, start.y, targetType);
        } else {
            boardStore.setTilesInRect(start.x, start.y, current.x, current.y, targetType);
        }
    } else {
        // Element placement mode (single tile toggle)
        if (isClick && isElementMode.value) {
            boardStore.toggleElementAtForCurrentTool(start.x, start.y);
        }
    }

    // Reset drag state
    isDragging.value = false;
    dragStart.value = null;
    dragCurrent.value = null;
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
    const el = boardStore.getElementAt(x, y);
    if (el) {
        return el.name || el.type;
    }
    const tile = boardStore.tiles[y]?.[x];
    if (tile && tile.type === TileType.Fixture) {
        return boardStore.getFixtureLabel(x, y);
    }
    return '';
});

onMounted(() => {
    if (props.board) {
        boardStore.hydrateBoard(props.board);
    } else {
        // If the store is empty, initialize a default board.
        // In a full application, this would be handled by Inertia props from the backend.
        if (boardStore.tiles.length === 0) {
            boardStore.initializeBoard(BoardDimension.Width, BoardDimension.Height);
        }
    }
    // Preload all icon images so rendering is stable
    Object.keys(iconMap).forEach((k) => prepareIcon(k));
});
</script>

<template>
    <div class="relative inline-block" :style="{ width: stageConfig.width + 'px', height: stageConfig.height + 'px' }">
        <!-- Konva stage (tiles + selection overlay) -->
        <v-stage :config="stageConfig" class="absolute inset-0" @mousedown="handleMouseDown" @mousemove="handleMouseMove" @mouseup="handleMouseUp">
            <!-- Base tiles layer -->
            <v-layer>
                <template v-for="(row, rowIndex) in boardStore.tiles" :key="rowIndex">
                    <v-rect v-for="tile in row" :key="tile.id" :config="getTileConfig(tile)" />
                </template>
            </v-layer>

            <!-- Elements layer -->
            <v-layer>
                <v-image
                    v-for="el in boardStore.elements"
                    :key="el.id"
                    :config="{
                        image: iconFor(el.type),
                        x: el.x * TILE_SIZE + (TILE_SIZE - ICON_SIZE) / 2,
                        y: el.y * TILE_SIZE + (TILE_SIZE - ICON_SIZE) / 2,
                        width: ICON_SIZE,
                        height: ICON_SIZE,
                        listening: false,
                    }"
                />
            </v-layer>
            
            <!-- Selection overlay layer -->
            <v-layer v-if="isSelectionMode">
                <v-rect :config="selectionRectConfig" />
            </v-layer>
            
        </v-stage>

        <!-- Tooltip for elements and fixtures -->
        <div v-if="tooltipText" class="absolute z-10" :style="{ left: ((mousePos?.x ?? 0) + 12) + 'px', top: ((mousePos?.y ?? 0) + 12) + 'px' }">
            <div class="pointer-events-none select-none rounded bg-neutral-900/90 text-white text-xs px-2 py-1 shadow-lg">
                {{ tooltipText }}
            </div>
        </div>
    </div>
</template>

<style scoped></style>
