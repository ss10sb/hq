<script setup lang="ts">
import EditBoardController from '@/actions/App/Http/Controllers/Board/EditBoardController';
import NewGameController from '@/actions/App/Http/Controllers/Game/NewGameController';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useBoardStore } from '@/stores/board';
import { BoardTool, FixtureType, MonsterType, TrapType } from '@/types/board';
import { router } from '@inertiajs/vue3';
import { Bomb, DoorClosed, Flag, Gem, Key, Lamp, Layers, Play, Skull, Square } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import EditFixtureOptions from './EditFixtureOptions.vue';
import EditMonsterOptions from './EditMonsterOptions.vue';
import EditTrapOptions from './EditTrapOptions.vue';

const isOpen = ref(true);
const boardStore = useBoardStore();

const isSaving = ref(false);

async function saveBoard(): Promise<void> {
    if (!boardStore?.id) {
        return;
    }
    try {
        isSaving.value = true;
        const payload = {
            name: boardStore.name,
            group: boardStore.group,
            order: boardStore.order,
            width: boardStore.width,
            height: boardStore.height,
            tiles: boardStore.tiles,
            elements: boardStore.elements,
        } as any;
        await router.put(EditBoardController.store.url(boardStore.id), payload, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                boardStore.markSaved();
            },
            onFinish: () => {
                isSaving.value = false;
            },
        } as any);
    } catch {
        isSaving.value = false;
    }
}

const isDirty = computed(() => boardStore.isDirty);

const canEdit = computed(() => boardStore.canEdit);

function startGame(): void {
    if (isDirty.value) {
        return; // guard: should not be clickable when dirty
    }
    router.get(NewGameController.url(boardStore.id), {}, { preserveScroll: true } as any);
}

const fixtureType = computed<FixtureType>({
    get() {
        return boardStore.currentFixtureType;
    },
    set(val: FixtureType) {
        boardStore.setCurrentFixtureSelection(val, boardStore.currentFixtureCustomText);
    },
});

const fixtureCustomText = computed<string>({
    get() {
        return boardStore.currentFixtureCustomText;
    },
    set(val: string) {
        boardStore.setCurrentFixtureSelection(boardStore.currentFixtureType, val);
    },
});

const trapType = computed<TrapType>({
    get() {
        return boardStore.currentTrapType;
    },
    set(val: TrapType) {
        boardStore.setCurrentTrapSelection(val, boardStore.currentTrapCustomText);
    },
});

const trapCustomText = computed<string>({
    get() {
        return boardStore.currentTrapCustomText;
    },
    set(val: string) {
        boardStore.setCurrentTrapSelection(boardStore.currentTrapType, val);
    },
});

// Monster selections
const monsterType = computed<MonsterType>({
    get() {
        return boardStore.currentMonsterType;
    },
    set(val: MonsterType) {
        // When changing the monster type, reset stats to that type's defaults
        // by NOT passing a stats override.
        boardStore.setCurrentMonsterSelection(val, boardStore.currentMonsterCustomText);
    },
});

const monsterCustomText = computed<string>({
    get() {
        return boardStore.currentMonsterCustomText;
    },
    set(val: string) {
        boardStore.setCurrentMonsterSelection(boardStore.currentMonsterType, val, boardStore.currentMonsterStats);
    },
});

const monsterStats = computed({
    get() {
        return boardStore.currentMonsterStats;
    },
    set(val: any) {
        boardStore.setCurrentMonsterSelection(boardStore.currentMonsterType, boardStore.currentMonsterCustomText, val);
    },
});

function openSidebar(): void {
    isOpen.value = true;
}

function closeSidebar(): void {
    isOpen.value = false;
}

function isTool(tool: BoardTool): boolean {
    return boardStore.currentTool === tool;
}

function setTool(tool: BoardTool): void {
    boardStore.setTool(tool);
}
</script>

<template>
    <!-- Floating open button (visible when closed) -->
    <button
        v-if="!isOpen"
        type="button"
        aria-label="Open tools sidebar"
        dusk="sidebar-open"
        class="fixed top-4 right-4 z-40 rounded-full border border-neutral-200 bg-white/90 p-2 shadow-lg transition hover:bg-white dark:border-neutral-800 dark:bg-neutral-900/90 dark:hover:bg-neutral-900"
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
                <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">Tools</h2>
                <button
                    type="button"
                    aria-label="Close tools sidebar"
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
                <div v-if="canEdit">
                    <TooltipProvider :delay-duration="0">
                        <div class="">
                            <h1 class="font-semibold text-neutral-700 dark:text-neutral-200">Tiles</h1>
                            <div class="my-5 grid grid-cols-3 gap-3">
                                <!-- tiles -->
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.DrawFloor)"
                                            :variant="isTool(BoardTool.DrawFloor) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Draw Floor"
                                            :aria-pressed="isTool(BoardTool.DrawFloor)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.DrawFloor)
                                                    ? 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/30 dark:text-blue-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Draw Floor</span>
                                            <Square
                                                :class="[
                                                    'size-5',
                                                    isTool(BoardTool.DrawFloor) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100',
                                                ]"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Draw Floor</p>
                                    </TooltipContent>
                                </Tooltip>

                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.AddFixture)"
                                            :variant="isTool(BoardTool.AddFixture) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Add Fixture"
                                            :aria-pressed="isTool(BoardTool.AddFixture)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.AddFixture)
                                                    ? 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/30 dark:text-blue-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Add Fixture</span>
                                            <Lamp
                                                :class="[
                                                    'size-5',
                                                    isTool(BoardTool.AddFixture) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100',
                                                ]"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Add Fixture</p>
                                    </TooltipContent>
                                </Tooltip>

                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.DrawWalls)"
                                            :variant="isTool(BoardTool.DrawWalls) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Draw Walls"
                                            :aria-pressed="isTool(BoardTool.DrawWalls)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.DrawWalls)
                                                    ? 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/30 dark:text-blue-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Draw Walls</span>
                                            <Layers
                                                :class="[
                                                    'size-5',
                                                    isTool(BoardTool.DrawWalls) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100',
                                                ]"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Draw Walls</p>
                                    </TooltipContent>
                                </Tooltip>
                            </div>
                        </div>

                        <div class="">
                            <h1 class="font-semibold text-neutral-700 dark:text-neutral-200">Elements</h1>
                            <div class="my-5 grid grid-cols-3 gap-3">
                                <!-- elements -->
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.AddMonster)"
                                            :variant="isTool(BoardTool.AddMonster) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Add Monster"
                                            :aria-pressed="isTool(BoardTool.AddMonster)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.AddMonster)
                                                    ? 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/30 dark:text-blue-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Add Monster</span>
                                            <Skull
                                                :class="[
                                                    'size-5',
                                                    isTool(BoardTool.AddMonster) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100',
                                                ]"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Add Monster</p>
                                    </TooltipContent>
                                </Tooltip>

                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.AddDoor)"
                                            :variant="isTool(BoardTool.AddDoor) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Add Door"
                                            :aria-pressed="isTool(BoardTool.AddDoor)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.AddDoor)
                                                    ? 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/30 dark:text-blue-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Add Door</span>
                                            <DoorClosed
                                                :class="['size-5', isTool(BoardTool.AddDoor) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100']"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Add Door</p>
                                    </TooltipContent>
                                </Tooltip>

                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.AddSecretDoor)"
                                            :variant="isTool(BoardTool.AddSecretDoor) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Add Secret Door"
                                            :aria-pressed="isTool(BoardTool.AddSecretDoor)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.AddSecretDoor)
                                                    ? 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/30 dark:text-blue-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Add Secret Door</span>
                                            <Key
                                                :class="[
                                                    'size-5',
                                                    isTool(BoardTool.AddSecretDoor) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100',
                                                ]"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Add Secret Door</p>
                                    </TooltipContent>
                                </Tooltip>

                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.AddTrap)"
                                            :variant="isTool(BoardTool.AddTrap) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Add Trap"
                                            :aria-pressed="isTool(BoardTool.AddTrap)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.AddTrap)
                                                    ? 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/30 dark:text-blue-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Add Trap</span>
                                            <Bomb
                                                :class="['size-5', isTool(BoardTool.AddTrap) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100']"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Add Trap</p>
                                    </TooltipContent>
                                </Tooltip>

                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.AddTreasure)"
                                            :variant="isTool(BoardTool.AddTreasure) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Add Treasure"
                                            :aria-pressed="isTool(BoardTool.AddTreasure)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.AddTreasure)
                                                    ? 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/30 dark:text-blue-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Add Treasure</span>
                                            <Gem
                                                :class="[
                                                    'size-5',
                                                    isTool(BoardTool.AddTreasure) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100',
                                                ]"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Add Treasure</p>
                                    </TooltipContent>
                                </Tooltip>

                                <!-- Player Start -->
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.AddPlayerStart)"
                                            :variant="isTool(BoardTool.AddPlayerStart) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Add Player Start"
                                            :aria-pressed="isTool(BoardTool.AddPlayerStart)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.AddPlayerStart)
                                                    ? 'bg-green-500/10 text-green-600 ring-1 ring-green-500/30 dark:text-green-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Add Player Start</span>
                                            <Play
                                                :class="[
                                                    'size-5',
                                                    isTool(BoardTool.AddPlayerStart) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100',
                                                ]"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Add Player Start</p>
                                    </TooltipContent>
                                </Tooltip>

                                <!-- Player Exit (unique) -->
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            @click="() => setTool(BoardTool.AddPlayerExit)"
                                            :variant="isTool(BoardTool.AddPlayerExit) ? 'secondary' : 'ghost'"
                                            size="icon"
                                            aria-label="Add Player Exit"
                                            :aria-pressed="isTool(BoardTool.AddPlayerExit)"
                                            :class="[
                                                'group h-9 w-9 cursor-pointer',
                                                isTool(BoardTool.AddPlayerExit)
                                                    ? 'bg-blue-500/10 text-blue-600 ring-1 ring-blue-500/30 dark:text-blue-400'
                                                    : '',
                                            ]"
                                        >
                                            <span class="sr-only">Add Player Exit</span>
                                            <Flag
                                                :class="[
                                                    'size-5',
                                                    isTool(BoardTool.AddPlayerExit) ? 'opacity-100' : 'opacity-80 group-hover:opacity-100',
                                                ]"
                                            />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Add Player Exit</p>
                                    </TooltipContent>
                                </Tooltip>
                            </div>
                        </div>
                        <!-- Monster options: visible only when Add Monster tool is active -->
                        <EditMonsterOptions
                            v-if="isTool(BoardTool.AddMonster)"
                            :type="monsterType"
                            :customText="monsterCustomText"
                            :stats="monsterStats"
                            @update:type="(v) => ((monsterType as any) = v)"
                            @update:customText="(v) => (monsterCustomText = v)"
                            @update:stats="(v) => (monsterStats = v)"
                        />

                        <!-- Fixture options: visible only when Add Fixture tool is active -->
                        <EditFixtureOptions
                            v-if="isTool(BoardTool.AddFixture)"
                            :type="fixtureType"
                            :customText="fixtureCustomText"
                            @update:type="(v) => ((fixtureType as any) = v)"
                            @update:customText="(v) => (fixtureCustomText = v)"
                        />

                        <!-- Trap options: visible only when Add Trap tool is active -->
                        <EditTrapOptions
                            v-if="isTool(BoardTool.AddTrap)"
                            :type="trapType"
                            :customText="trapCustomText"
                            @update:type="(v) => ((trapType as any) = v)"
                            @update:customText="(v) => (trapCustomText = v)"
                        />
                    </TooltipProvider>
                </div>
                <div
                    v-else
                    class="rounded-md border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-200"
                >
                    This board is view only.
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="mt-auto border-t border-neutral-200 p-4 dark:border-neutral-800">
                <div class="flex gap-2">
                    <Button v-if="canEdit" type="button" class="flex-1" :disabled="isSaving" @click="saveBoard">
                        {{ isSaving ? 'Saving...' : 'Save Board' }}
                    </Button>
                    <Button v-if="!isDirty" type="button" class="flex-1" variant="secondary" @click="startGame"> Start Game </Button>
                </div>
            </div>
        </aside>
    </div>
</template>

<style scoped></style>
