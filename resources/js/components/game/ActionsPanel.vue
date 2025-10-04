<script setup lang="ts">
import type { DieType } from '@/lib/game/dice';
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    currentUserId: number | null;
    isSelectActive?: boolean;
    canEndTurn?: boolean;
    activeHeroAttackDice?: number;
}>();

const emit = defineEmits<{
    (e: 'roll-dice', payload: { type: DieType; count: number }): void;
    (e: 'end-turn'): void;
    (e: 'toggle-select'): void;
}>();

const showDice = ref(false);
const showMove = ref(false);
const showMoveHelp = ref(false);
const dieType = ref<DieType>('six_sided' as any);
const dieCount = ref<number>(2);

function setDefaultCount(): void {
    if (dieType.value === ('combat' as any)) {
        const atk = Math.max(1, Math.min(10, Number(props.activeHeroAttackDice ?? 1)));
        dieCount.value = atk;
    } else {
        dieCount.value = 2; // six-sided defaults to 2
    }
}

function toggleMove(): void {
    if (props.isSelectActive) {
        emit('toggle-select');
    }
    showMove.value = !showMove.value;
}

function toggleMoveHelp(): void {
    showMoveHelp.value = !showMoveHelp.value;
}

function toggleDice(): void {
    showDice.value = !showDice.value;
    if (showDice.value) {
        setDefaultCount();
    }
}

function toggleSelect(): void {
    if (showMove.value) {
        showMove.value = false;
    }
    emit('toggle-select');
}

function decCount(): void {
    dieCount.value = Math.max(1, (Number(dieCount.value) || 1) - 1);
}

function incCount(): void {
    dieCount.value = Math.min(10, (Number(dieCount.value) || 1) + 1);
}

watch(dieType, () => {
    // When switching types, reset to sensible defaults per requirements
    setDefaultCount();
});

watch(
    () => props.activeHeroAttackDice,
    () => {
        // If showing dice and on combat type, refresh default when hero changes
        if (showDice.value && dieType.value === ('combat' as any)) {
            setDefaultCount();
        }
    }
);

onMounted(() => {
    setDefaultCount();
});

function doRoll(): void {
    const count = Math.max(1, Math.min(10, Number(dieCount.value) || 1));
    emit('roll-dice', { type: dieType.value as any, count });
    // Keep the dice panel open after rolling; user can Cancel or click Roll Dice again to close
}
</script>

<template>
    <div>
        <div class="mb-1 text-xs text-gray-500 uppercase">Actions</div>
        <div class="flex flex-col gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <button class="rounded bg-gray-100 px-3 py-1 text-sm dark:bg-neutral-800" @click="toggleDice">Roll
                    Dice
                </button>
                <button
                    class="rounded px-3 py-1 text-sm"
                    :class="props.isSelectActive ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-neutral-800'"
                    @click="toggleSelect"
                >
                    Select
                </button>
                <button v-if="true"
                        @click="toggleMove"
                        class="rounded px-3 py-1 text-sm"
                        :class="showMove ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-neutral-800'"
                >Move
                </button>
                <button v-if="props.canEndTurn === true"
                        class="rounded bg-gray-100 px-3 py-1 text-sm dark:bg-neutral-800" @click="emit('end-turn')">
                    End Turn
                </button>
            </div>
            <div v-if="showMove"
                 class="rounded border border-gray-200 bg-gray-50 p-2 dark:border-neutral-800 dark:bg-neutral-800/50">
                <div class="flex flex-col gap-2 text-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div v-if="props.canEndTurn" class="text-green-500">You can move.</div>
                        <div v-else class="text-red-500">It is not your turn.</div>
                        <button
                            @click="toggleMoveHelp"
                            type="button" class="text-left underline decoration-dotted hover:decoration-solid cursor-pointer"
                                >Help</button>
                    </div>
                    <div v-if="showMoveHelp">
                        Moving: when it is your turn, click on your hero. Move the mouse along the path you with your
                        hero to travel.
                        Click the tile where you want to end your move. Your hero will then move to that tile. To cancel
                        your move,
                        click the your hero icon again. Note that you do not have hold the mouse button down to move.
                    </div>
                </div>
            </div>
            <div v-if="showDice"
                 class="rounded border border-gray-200 bg-gray-50 p-2 dark:border-neutral-800 dark:bg-neutral-800/50">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <div class="inline-flex items-center gap-2">
                        <div class="inline-flex overflow-hidden rounded border border-gray-200 dark:border-neutral-700">
                            <span class="sr-only">Type</span>
                            <button
                                aria-description="Click to select 6-sided dice"
                                type="button"
                                class="px-3 py-1 text-sm"
                                :class="
                                    dieType === ('six_sided' as any)
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200'
                                "
                                @click="dieType = 'six_sided' as any"
                            >
                                6-sided
                            </button>
                            <button
                                type="button"
                                aria-description="Click to select combat dice"
                                class="px-3 py-1 text-sm"
                                :class="
                                    dieType === ('combat' as any)
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200'
                                "
                                @click="dieType = 'combat' as any"
                            >
                                Combat
                            </button>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-1">
                        <div class="flex items-center gap-1">
                            <span class="sr-only">Count</span>
                            <button
                                aria-description="Click to decrement"
                                type="button"
                                class="rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-neutral-800"
                                :disabled="(dieCount ?? 1) <= 1"
                                @click="decCount"
                            >
                                −
                            </button>
                            <span class="min-w-6 text-center">{{ dieCount }}</span>
                            <button
                                aria-description="Click to increment"
                                type="button"
                                class="rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-neutral-800"
                                :disabled="(dieCount ?? 1) >= 10"
                                @click="incCount"
                            >
                                +
                            </button>
                        </div>
                    </div>
                    <div class="ml-auto flex gap-2">
                        <button type="button" class="rounded bg-gray-100 px-3 py-1 dark:bg-neutral-700"
                                @click="showDice = false">Cancel
                        </button>
                        <button type="button" class="rounded bg-blue-600 px-3 py-1 text-white" @click="doRoll">Roll
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
