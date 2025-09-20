<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import type { DieType } from '@/lib/board/game';

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

function toggleDice(): void {
    showDice.value = !showDice.value;
    if (showDice.value) {
        setDefaultCount();
    }
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

watch(() => props.activeHeroAttackDice, (v) => {
    // If showing dice and on combat type, refresh default when hero changes
    if (showDice.value && dieType.value === ('combat' as any)) {
        setDefaultCount();
    }
});

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
        <div class="text-xs uppercase text-gray-500 mb-1">Actions</div>
        <div class="flex flex-col gap-2">
            <div class="flex flex-wrap gap-2 items-center">
                <button class="px-3 py-1 rounded bg-gray-100 dark:bg-neutral-800 text-sm" @click="toggleDice">
                    Roll Dice
                </button>
                <button
                    class="px-3 py-1 rounded text-sm"
                    :class="props.isSelectActive ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-neutral-800'"
                    @click="emit('toggle-select')"
                >
                    Select
                </button>
                <button
                    v-if="props.canEndTurn === true"
                    class="px-3 py-1 rounded bg-gray-100 dark:bg-neutral-800 text-sm"
                    @click="emit('end-turn')"
                >
                    End Turn
                </button>
            </div>
            <div v-if="showDice" class="rounded border border-gray-200 dark:border-neutral-800 p-2 bg-gray-50 dark:bg-neutral-800/50">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <div class="inline-flex items-center gap-2">
                        <div class="inline-flex rounded overflow-hidden border border-gray-200 dark:border-neutral-700">
                            <span class="sr-only">Type</span>
                            <button
                                aria-description="Click to select 6-sided dice"
                                type="button"
                                class="px-3 py-1 text-sm"
                                :class="dieType === ('six_sided' as any) ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200'"
                                @click="dieType = 'six_sided' as any"
                            >6-sided</button>
                            <button
                                type="button"
                                aria-description="Click to select combat dice"
                                class="px-3 py-1 text-sm"
                                :class="dieType === ('combat' as any) ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200'"
                                @click="dieType = 'combat' as any"
                            >Combat</button>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-1">
                        <div class="flex items-center gap-1">
                            <span class="sr-only">Count</span>
                            <button
                                aria-description="Click to decrement"
                                type="button"
                                class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-neutral-800"
                                :disabled="(dieCount ?? 1) <= 1"
                                @click="decCount"
                            >−</button>
                            <span class="min-w-6 text-center">{{ dieCount }}</span>
                            <button
                                aria-description="Click to increment"
                                type="button"
                                class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-neutral-800"
                                :disabled="(dieCount ?? 1) >= 10"
                                @click="incCount"
                            >+</button>
                        </div>
                    </div>
                    <div class="ml-auto flex gap-2">
                        <button type="button" class="px-3 py-1 rounded bg-gray-100 dark:bg-neutral-700" @click="showDice=false">Cancel</button>
                        <button type="button" class="px-3 py-1 rounded bg-blue-600 text-white" @click="doRoll">Roll</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
