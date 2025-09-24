<script setup lang="ts">
import EditFixtureOptions from '@/components/board/EditFixtureOptions.vue';
import EditMonsterOptions from '@/components/board/EditMonsterOptions.vue';
import EditTrapOptions from '@/components/board/EditTrapOptions.vue';
import { useBoardStore } from '@/stores/board';
import { BoardTool, FixtureType, MonsterType, TrapType } from '@/types/board';
import { Bomb, DoorClosed, Eye, EyeOff, Flag, Gem, Key, Move, Play, Skull, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    isOpen?: boolean;
}>();
const isOpen = ref(props.isOpen ?? false);
const showEditTools = ref(false);
const toolSelectedClasses = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-900/50';
const toolDeselectedClasses = 'bg-white dark:bg-neutral-900 border-neutral-200 dark:border-neutral-800';
const boardStore = useBoardStore();

const isToggleVisibility = computed(() => boardStore.currentTool === BoardTool.ToggleVisibility);
const isOpenDoor = computed(() => boardStore.currentTool === BoardTool.OpenDoor);
const isRevealRoom = computed(() => boardStore.currentTool === BoardTool.RevealRoom);
const isRevealCorridor = computed(() => boardStore.currentTool === BoardTool.RevealCorridor);
const isRemoveElement = computed(() => boardStore.currentTool === BoardTool.RemoveElement);
const isMoveMonster = computed(() => boardStore.currentTool === BoardTool.MoveMonster);
const isMoveElement = computed(() => boardStore.currentTool === BoardTool.MoveElement);
const isDrawFloor = computed(() => boardStore.currentTool === BoardTool.DrawFloor);
const isAddFixture = computed(() => boardStore.currentTool === BoardTool.AddFixture);
const isDrawWalls = computed(() => boardStore.currentTool === BoardTool.DrawWalls);

// Add Element menu toggle; open when any Add* tool is active
const isAddMenuOpen = ref(false);

// Proxies for options components (reuse board store state like EditBoardSidebar)
const monsterType = computed<MonsterType>({
    get() {
        return boardStore.currentMonsterType;
    },
    set(v: MonsterType) {
        boardStore.setCurrentMonsterSelection(v, boardStore.currentMonsterCustomText);
    },
});
const monsterCustomText = computed<string>({
    get() {
        return boardStore.currentMonsterCustomText;
    },
    set(v: string) {
        boardStore.setCurrentMonsterSelection(boardStore.currentMonsterType, v, boardStore.currentMonsterStats);
    },
});
const monsterStats = computed<any>({
    get() {
        return boardStore.currentMonsterStats;
    },
    set(v: any) {
        boardStore.setCurrentMonsterSelection(boardStore.currentMonsterType, boardStore.currentMonsterCustomText, v);
    },
});

const trapType = computed<TrapType>({
    get() {
        return boardStore.currentTrapType;
    },
    set(v: TrapType) {
        boardStore.setCurrentTrapSelection(v, boardStore.currentTrapCustomText);
    },
});
const trapCustomText = computed<string>({
    get() {
        return boardStore.currentTrapCustomText;
    },
    set(v: string) {
        boardStore.setCurrentTrapSelection(boardStore.currentTrapType, v);
    },
});

// Fixture selections
const fixtureType = computed<FixtureType>({
    get() {
        return boardStore.currentFixtureType;
    },
    set(v: FixtureType) {
        boardStore.setCurrentFixtureSelection(v, boardStore.currentFixtureCustomText);
    },
});
const fixtureCustomText = computed<string>({
    get() {
        return boardStore.currentFixtureCustomText;
    },
    set(v: string) {
        boardStore.setCurrentFixtureSelection(boardStore.currentFixtureType, v);
    },
});

function openSidebar(): void {
    isOpen.value = true;
}

function closeSidebar(): void {
    isOpen.value = false;
}

function setTool(tool: BoardTool): void {
    boardStore.setTool(tool);
    // Opening the add element grid when selecting any Add* tool
    if (
        [
            BoardTool.AddMonster,
            BoardTool.AddDoor,
            BoardTool.AddSecretDoor,
            BoardTool.AddTrap,
            BoardTool.AddTreasure,
            BoardTool.AddPlayerStart,
            BoardTool.AddPlayerExit,
        ].includes(tool)
    ) {
        isAddMenuOpen.value = true;
    }
}
</script>

<template>
    <button
        v-if="!isOpen"
        type="button"
        aria-label="Open gamemaster tools sidebar"
        dusk="sidebar-open"
        class="fixed top-3 right-2 z-40 rounded-full border bg-white/90 p-2 shadow-lg transition hover:bg-white dark:bg-neutral-900/90 dark:hover:bg-neutral-900"
        :class="boardStore.currentTool !== BoardTool.None ? 'border-2 border-blue-500' : 'border-neutral-200 dark:border-neutral-800'"
        @click="openSidebar"
    >
        <!-- Heroicon: Chevron Left -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
            <path
                fill-rule="evenodd"
                d="M15.53 4.47a.75.75 0 0 1 0 1.06L9.56 11.5l5.97 5.97a.75.75 0 1 1-1.06 1.06l-6.5-6.5a.75.75 0 0 1 0-1.06l6.5-6.5a.75.75 0 0 1 1.06 0Z"
                clip-rule="evenodd"
            />
        </svg>
    </button>
    <!-- Sidebar Wrapper: pointer-events-none ensures clicks outside are not blocked -->
    <div class="pointer-events-none fixed inset-y-0 right-0 z-40">
        <!-- Panel -->
        <aside
            class="pointer-events-auto flex h-screen w-[25vw] max-w-[480px] min-w-[280px] transform flex-col border-l border-neutral-200 bg-white shadow-xl transition-transform duration-300 ease-out dark:border-neutral-800 dark:bg-neutral-900"
            :class="isOpen ? 'translate-x-0' : 'translate-x-full'"
        >
            <!-- Header with close button -->
            <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 dark:border-neutral-800">
                <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">GM Tools</h2>
                <button
                    type="button"
                    aria-label="Close gamemaster tools sidebar"
                    dusk="sidebar-close"
                    class="rounded-md p-2 transition hover:bg-neutral-100 dark:hover:bg-neutral-800"
                    @click="closeSidebar"
                >
                    <!-- Heroicon: Chevron Right -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                        <path
                            fill-rule="evenodd"
                            d="M8.47 4.47a.75.75 0 0 1 1.06 0l6.5 6.5a.75.75 0 0 1 0 1.06l-6.5 6.5a.75.75 0 1 1-1.06-1.06L14.44 12 8.47 6.03a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="@container overflow-y-auto p-4">
                <div class="flex flex-col gap-3">
                    <div>
                        <div class="mb-2 text-xs text-gray-500 uppercase">Reveal</div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rounded border px-3 py-2 text-sm"
                                :class="isRevealRoom ? toolSelectedClasses : toolDeselectedClasses"
                                @click="setTool(BoardTool.RevealRoom)"
                            >
                                Reveal Room
                            </button>
                            <button
                                type="button"
                                class="rounded border px-3 py-2 text-sm"
                                :class="isRevealCorridor ? toolSelectedClasses : toolDeselectedClasses"
                                @click="setTool(BoardTool.RevealCorridor)"
                            >
                                Reveal Corridor
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-neutral-500">
                            Click a room tile to reveal the bounded room and its doors; or click a corridor tile to reveal straight lines until
                            walls/doors.
                        </p>
                    </div>
                    <div>
                        <div class="mb-2 text-xs text-gray-500 uppercase">Doors</div>
                        <button
                            type="button"
                            class="rounded border px-3 py-2 text-sm"
                            :class="isOpenDoor ? toolSelectedClasses : toolDeselectedClasses"
                            @click="setTool(BoardTool.OpenDoor)"
                        >
                            <span>Open Door</span>
                        </button>
                        <p class="mt-1 text-xs text-neutral-500">
                            Click a closed door or secret door to open it. Secret doors become visible when opened.
                        </p>
                    </div>
                    <div>
                        <div class="mb-2 text-xs text-gray-500 uppercase">Movement</div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                :class="isMoveMonster ? toolSelectedClasses : toolDeselectedClasses"
                                @click="setTool(BoardTool.MoveMonster)"
                            >
                                <Move class="size-4" />
                                <span>Move Monster</span>
                            </button>
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                :class="isMoveElement ? toolSelectedClasses : toolDeselectedClasses"
                                @click="setTool(BoardTool.MoveElement)"
                            >
                                <Move class="size-4" />
                                <span>Move Element</span>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-neutral-500">
                            Move Monster: click a monster, hover to preview path, click to move. Move Element: click an element, then click a
                            destination tile to relocate it.
                        </p>
                    </div>
                    <div>
                        <div class="mb-2 text-xs text-gray-500 uppercase">Elements</div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                :class="isToggleVisibility ? toolSelectedClasses : toolDeselectedClasses"
                                @click="setTool(BoardTool.ToggleVisibility)"
                            >
                                <Eye v-if="!isToggleVisibility" class="size-4" />
                                <EyeOff v-else class="size-4" />
                                <span>Toggle Visibility</span>
                            </button>
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                :class="isRemoveElement ? toolSelectedClasses : toolDeselectedClasses"
                                @click="setTool(BoardTool.RemoveElement)"
                            >
                                <Trash2 class="size-4" />
                                <span>Remove Element</span>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-neutral-500">
                            Toggle Visibility: click an element to hide/show it for players. Hidden elements appear dimmed for you. Remove Element:
                            Click any element to delete it from the board.
                        </p>
                    </div>
                    <!-- Edit tools -->
                    <div>
                        <button
                            type="button"
                            class="rounded border px-3 py-2 text-sm"
                            :class="
                                showEditTools
                                    ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-900/30 dark:text-amber-300'
                                    : 'border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900'
                            "
                            @click="showEditTools = !showEditTools"
                        >
                            {{ showEditTools ? 'Hide Edit Tools' : 'Show Edit Tools' }}
                        </button>
                    </div>
                    <div v-if="showEditTools">
                        <!-- Tiles section -->
                        <div class="mb-2">
                            <div class="mb-2 text-xs text-gray-500 uppercase">Tiles</div>
                            <div class="mb-2 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="rounded border px-3 py-2 text-sm"
                                    :class="isDrawFloor ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="setTool(BoardTool.DrawFloor)"
                                >
                                    Draw Floor
                                </button>
                                <button
                                    type="button"
                                    class="rounded border px-3 py-2 text-sm"
                                    :class="isAddFixture ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="setTool(BoardTool.AddFixture)"
                                >
                                    Add Fixture
                                </button>
                                <button
                                    type="button"
                                    class="rounded border px-3 py-2 text-sm"
                                    :class="isDrawWalls ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="setTool(BoardTool.DrawWalls)"
                                >
                                    Draw Walls
                                </button>
                            </div>
                            <EditFixtureOptions
                                v-if="boardStore.currentTool === BoardTool.AddFixture"
                                :type="fixtureType"
                                :customText="fixtureCustomText"
                                @update:type="(v) => ((fixtureType as any) = v)"
                                @update:customText="(v) => (fixtureCustomText = v)"
                            />
                        </div>
                        <!-- Add Element section -->
                        <div>
                            <div class="mb-2 text-xs text-gray-500 uppercase">Add Element</div>
                            <div class="mb-2 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                    :class="boardStore.currentTool === BoardTool.AddMonster ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="
                                        () => {
                                            isAddMenuOpen = true;
                                            setTool(BoardTool.AddMonster);
                                        }
                                    "
                                >
                                    <Skull class="size-4" />
                                    <span>Monster</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                    :class="boardStore.currentTool === BoardTool.AddDoor ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="
                                        () => {
                                            isAddMenuOpen = true;
                                            setTool(BoardTool.AddDoor);
                                        }
                                    "
                                >
                                    <DoorClosed class="size-4" />
                                    <span>Door</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                    :class="boardStore.currentTool === BoardTool.AddSecretDoor ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="
                                        () => {
                                            isAddMenuOpen = true;
                                            setTool(BoardTool.AddSecretDoor);
                                        }
                                    "
                                >
                                    <Key class="size-4" />
                                    <span>Secret Door</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                    :class="boardStore.currentTool === BoardTool.AddTrap ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="
                                        () => {
                                            isAddMenuOpen = true;
                                            setTool(BoardTool.AddTrap);
                                        }
                                    "
                                >
                                    <Bomb class="size-4" />
                                    <span>Trap</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                    :class="boardStore.currentTool === BoardTool.AddTreasure ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="
                                        () => {
                                            isAddMenuOpen = true;
                                            setTool(BoardTool.AddTreasure);
                                        }
                                    "
                                >
                                    <Gem class="size-4" />
                                    <span>Treasure</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                    :class="boardStore.currentTool === BoardTool.AddPlayerStart ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="
                                        () => {
                                            isAddMenuOpen = true;
                                            setTool(BoardTool.AddPlayerStart);
                                        }
                                    "
                                >
                                    <Play class="size-4" />
                                    <span>Player Start</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded border px-3 py-2 text-sm"
                                    :class="boardStore.currentTool === BoardTool.AddPlayerExit ? toolSelectedClasses : toolDeselectedClasses"
                                    @click="
                                        () => {
                                            isAddMenuOpen = true;
                                            setTool(BoardTool.AddPlayerExit);
                                        }
                                    "
                                >
                                    <Flag class="size-4" />
                                    <span>Player Exit</span>
                                </button>
                            </div>
                            <!-- Inline options mirroring EditBoardSidebar -->
                            <EditMonsterOptions
                                v-if="boardStore.currentTool === BoardTool.AddMonster"
                                :type="monsterType"
                                :customText="monsterCustomText"
                                :stats="monsterStats"
                                @update:type="(v) => ((monsterType as any) = v)"
                                @update:customText="(v) => (monsterCustomText = v)"
                                @update:stats="(v) => (monsterStats = v)"
                            />
                            <EditTrapOptions
                                v-if="boardStore.currentTool === BoardTool.AddTrap"
                                :type="trapType"
                                :customText="trapCustomText"
                                @update:type="(v) => ((trapType as any) = v)"
                                @update:customText="(v) => (trapCustomText = v)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</template>

<style scoped></style>
