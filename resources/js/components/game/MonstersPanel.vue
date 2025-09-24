<script setup lang="ts">
import type { Element } from '@/types/board';
import { Brain, Heart, Shield, SwordIcon, X } from 'lucide-vue-next';

const props = defineProps<{
    selectedMonster: Element | null;
    isGameMaster: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update-body', payload: { elementId: string; value: number }): void;
}>();

function incBody(): void {
    const m = props.selectedMonster;
    if (!m || !m.stats) {
        return;
    }
    const cur = Number(m.stats.currentBodyPoints || 0);
    emit('update-body', { elementId: m.id, value: cur + 1 });
}

function decBody(): void {
    const m = props.selectedMonster;
    if (!m || !m.stats) {
        return;
    }
    const cur = Number(m.stats.currentBodyPoints || 0);
    emit('update-body', { elementId: m.id, value: Math.max(0, cur - 1) });
}
</script>

<template>
    <div class="mb-4">
        <div class="mb-1 flex items-center justify-between gap-2">
            <div class="text-xs text-gray-500 uppercase">Monsters</div>
            <button v-if="selectedMonster" type="button" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-neutral-800" @click="emit('close')">
                <X class="size-4" />
            </button>
        </div>

        <template v-if="!selectedMonster">
            <div class="text-sm text-gray-600 dark:text-gray-300">Click a monster on the board to view details.</div>
        </template>
        <template v-else>
            <div class="rounded-md border border-gray-200 bg-gray-50 p-2 dark:border-neutral-800 dark:bg-neutral-900/50">
                <div class="flex items-center justify-between">
                    <div class="font-medium" :class="{ 'text-gray-500': selectedMonster.stats?.currentBodyPoints === 0 }">
                        {{ selectedMonster.name }}
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-sm">
                        <Heart class="size-4 text-red-500" />
                        <template v-if="isGameMaster">
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-neutral-800"
                                    :disabled="(selectedMonster.stats?.currentBodyPoints ?? 0) <= 0"
                                    @click="decBody"
                                >
                                    −
                                </button>
                                <span class="min-w-6 text-center">{{ selectedMonster.stats?.currentBodyPoints ?? 0 }}</span>
                                <span class="text-xs text-neutral-500">/ {{ selectedMonster.stats?.bodyPoints ?? 0 }}</span>
                                <button type="button" class="rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-neutral-800" @click="incBody">+</button>
                            </div>
                        </template>
                        <template v-else>
                            <span class="min-w-6 text-center">{{ selectedMonster.stats?.currentBodyPoints ?? 0 }}</span>
                            <span class="text-xs text-neutral-500">/ {{ selectedMonster.stats?.bodyPoints ?? 0 }}</span>
                        </template>
                    </div>
                </div>
                <div class="mt-2 flex flex-row items-center gap-5 text-sm text-gray-800 dark:text-white">
                    <div class="flex flex-row items-center gap-2">
                        <Brain :size="18" class="text-blue-500" />
                        <div>{{ selectedMonster.stats?.mindPoints ?? 0 }}</div>
                    </div>
                    <div class="flex flex-row items-center gap-2">
                        <SwordIcon :size="18" class="text-red-500" />
                        <div>{{ selectedMonster.stats?.attackDice ?? 0 }}</div>
                    </div>
                    <div class="flex flex-row items-center gap-2">
                        <Shield :size="18" class="text-green-500" />
                        <div>{{ selectedMonster.stats?.defenseDice ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped></style>
