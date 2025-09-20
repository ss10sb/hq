<script setup lang="ts">
import type { Player } from '@/types/game';
import { Brain, Footprints, Heart, Shield, SwordIcon } from 'lucide-vue-next';
import type { NewHero } from '@/types/hero';
import { Hero, HeroArchetype, Zargon } from '@/types/hero';
import { isHero } from '@/lib/board/game';
import { heroColorById } from '@/lib/game/colors';

import HeroForm from '@/components/hero/HeroForm.vue';
import { ref } from 'vue';

const props = defineProps<{
    heroes: (Hero | Zargon)[];
    players: Player[];
    activeHeroId: number;
    isGameMaster: boolean;
    presentIds: number[];
    currentUserId: number | null;
    stepsByHero?: Record<number, number>;
    previewStepsByHero?: Record<number, number>;
}>();

const emit = defineEmits<{
    (e: 'move-up', index: number): void;
    (e: 'move-down', index: number): void;
    (e: 'set-active', heroId: number): void;
    (e: 'update-body', payload: { heroId: number | null; value: number }): void;
    (e: 'save-hero', payload: { heroId: number; hero: NewHero }): void;
    (e: 'assign-hero', payload: { heroId: number; playerId: number }): void;
}>();

const editingHeroId = ref<number | null>(null);
const editModel = ref<NewHero | null>(null);
const gmPlayerActionsActive = ref(false);
const showReassign = ref(false);

function toNewHero(h: Hero): NewHero {
    return {
        name: h.name,
        type: h.type,
        stats: { ...h.stats },
        inventory: (h.inventory || []).map((it: any) => ({ ...it })),
        equipment: (h.equipment || []).map((it: any) => ({ ...it }))
    } as NewHero;
}

function startEdit(h: Hero): void {
    if (!isHero(h)) {
        return;
    }
    editingHeroId.value = h.id;
    editModel.value = toNewHero(h);
}

function cancelEdit(): void {
    editingHeroId.value = null;
    editModel.value = null;
}

function saveEdit(heroId: number): void {
    if (editModel.value == null) {
        return;
    }
    emit('save-hero', { heroId, hero: editModel.value });
    editingHeroId.value = null;
    editModel.value = null;
}

function playerIsPresent(playerId: number): boolean {
    const pid = Number(playerId);
    if (!Number.isFinite(pid)) {
        return false;
    }
    // Coerce to number to avoid strict-equality mismatches if IDs arrive as strings
    return props.presentIds.some((id) => Number(id) === pid);
}

function canEditHero(h: Hero): boolean {
    if (!isHero(h)) {
        return false;
    }
    return props.currentUserId != null && h.playerId === props.currentUserId;
}

function bodyDisplay(h: Hero): { current: number | null; max: number | null; over: boolean } {
    const current = h.stats?.currentBodyPoints ?? null;
    const max = h.stats?.bodyPoints ?? null;
    const over = current !== null && max !== null && current > max;
    return { current, max, over };
}

function incBody(h: Hero): void {
    const current = h.stats?.currentBodyPoints ?? 0;
    emit('update-body', { heroId: h.id ?? null, value: current + 1 });
}

function decBody(h: Hero): void {
    const current = h.stats?.currentBodyPoints ?? 0;
    emit('update-body', { heroId: h.id ?? null, value: Math.max(0, current - 1) });
}

function stepsFor(h: Hero | Zargon): number {
    const id = (h as any)?.id as number | undefined;
    if (typeof id !== 'number') {
        return 0;
    }
    const preview = (props.previewStepsByHero || {})[id];
    if (typeof preview === 'number') {
        return Math.max(0, preview);
    }
    const committed = (props.stepsByHero || {})[id];
    return Math.max(0, committed ?? 0);
}

function heroListOnly(): Hero[] {
    return (props.heroes || []).filter((h: any) => isHero(h)) as Hero[];
}

function onAssign(heroId: number, playerIdStr: string): void {
    const playerId = Number(playerIdStr);
    if (!Number.isFinite(playerId)) { return; }
    emit('assign-hero', { heroId, playerId });
}
</script>

<template>
    <div class="mb-4">
        <div class="flex items-center justify-between gap-2 mb-1">
            <div class="text-xs uppercase text-gray-500">Players</div>
            <div v-if="isGameMaster" class="flex items-center gap-2">
                <button
                    type="button"
                    class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 disabled:opacity-50"
                    @click="gmPlayerActionsActive = !gmPlayerActionsActive"
                >Actions
                </button>
                <button
                    type="button"
                    class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-neutral-800"
                    @click="showReassign = !showReassign"
                >{{ showReassign ? 'Hide' : 'Reassign' }}</button>
            </div>
        </div>

        <!-- Reassign form (GM only), shown above the list -->
        <div v-if="isGameMaster && showReassign" class="mb-2 p-2 border rounded-md border-gray-200 dark:border-neutral-800 bg-gray-50 dark:bg-neutral-900/50">
            <div class="flex flex-col gap-2">
                <div v-for="h in heroListOnly()" :key="(h as any).id" class="flex items-center gap-2">
                    <div class="flex-1 text-sm">{{ (h as any).name }}</div>
                    <select
                        class="text-sm rounded border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-2 py-1"
                        :value="(h as any).playerId"
                        @change="onAssign((h as any).id, ($event.target as HTMLSelectElement).value)"
                    >
                        <option v-for="p in props.players" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <ul class="flex flex-col gap-1">
            <li
                v-for="(h, idx) in heroes"
                :key="h.id"
                class="gap-2 p-1"
                :class="h.id === activeHeroId ? 'border rounded-md border-blue-500' : ''"
            >
                <div class="flex items-center gap-1">
                <span
                    class="inline-block h-3 w-3 rounded-full"
                    :class="playerIsPresent(h.playerId) ? 'bg-green-500' : 'bg-gray-400'"
                />
                    <span class="flex-1" :style="isHero(h) ? { color: heroColorById(h.id) } : {}">
                    {{ h.name }}
                </span>

                    <!-- Body Points -->
                    <div v-if="isHero(h)" class="flex items-center gap-1 text-sm">
                        <Heart class="size-4 text-red-500" />
                        <template v-if="canEditHero(h as any)">
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-neutral-800"
                                    :disabled="(h.stats?.currentBodyPoints ?? 0) <= 0"
                                    @click="decBody(h as any)"
                                >−
                                </button>
                                <span class="min-w-6 text-center">{{ h.stats?.currentBodyPoints ?? 0 }}</span>
                                <span class="text-xs text-neutral-500">/ {{ h.stats?.bodyPoints ?? 0 }}</span>
                                <button
                                    type="button"
                                    class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-neutral-800"
                                    @click="incBody(h as any)"
                                >+
                                </button>
                            </div>
                        </template>
                        <template v-else>
                            <span class="min-w-6 text-center">{{ h.stats?.currentBodyPoints ?? 0 }}</span>
                            <span class="text-xs text-neutral-500">/ {{ h.stats?.bodyPoints ?? 0 }}</span>
                        </template>
                        <span v-if="bodyDisplay(h as any).over"
                              class="ml-1 inline-flex items-center justify-center text-[10px] font-semibold text-rose-600 dark:text-rose-400">
                            +
                        </span>
                    </div>

                    <div v-if="isGameMaster && gmPlayerActionsActive" class="flex items-center gap-1">
                        <button
                            type="button"
                            class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-neutral-800"
                            :disabled="idx === 0"
                            @click="emit('move-up', idx)"
                        >↑
                        </button>
                        <button
                            type="button"
                            class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-neutral-800"
                            :disabled="idx === players.length - 1"
                            @click="emit('move-down', idx)"
                        >↓
                        </button>
                        <button
                            type="button"
                            class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 disabled:opacity-50"
                            :disabled="h.id === activeHeroId"
                            @click="emit('set-active', h.id)"
                        >Set Active
                        </button>
                    </div>
                    <div v-if="isHero(h) && canEditHero(h as any)" class="ml-2">
                        <button
                            type="button"
                            class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 disabled:opacity-50"
                            @click="startEdit(h as any)"
                        >Edit
                        </button>
                    </div>
                </div>

                <div v-if="isHero(h) && editingHeroId === h.id"
                     class="mt-2 p-2 border rounded-md border-gray-200 dark:border-neutral-800 bg-gray-50 dark:bg-neutral-900/50">
                    <HeroForm
                        :model-value="(editModel as any)"
                        mode="edit"
                        :archetypes="(Object.values(HeroArchetype) as any)"
                        @update:modelValue="(v: any) => (editModel = v)"
                        @submit="saveEdit((h as any).id)"
                        @cancel="cancelEdit"
                    />
                </div>

                <div v-if="isHero(h) && h.id === activeHeroId"
                     class="mt-2 px-2 flex flex-row justify-between flex-wrap items-center gap-3 text-sm text-neutral-500">
                    <div>{{ h.type }}</div>
                    <div class="flex flex-row items-center gap-5 text-gray-800 dark:text-white">
                        <div class="flex flex-row items-center gap-2">
                            <Footprints :size="18" class="text-purple-500" />
                            <!-- steps for this hero -->
                            <div>{{ stepsFor(h as any) }}</div>
                        </div>
                        <div class="flex flex-row items-center gap-2">
                            <Brain :size="18" class="text-blue-500" />
                            <div>{{ h.stats.mindPoints }}</div>
                        </div>
                        <div class="flex flex-row items-center gap-2">
                            <SwordIcon :size="18" class="text-red-500" />
                            <div>{{ h.stats.attackDice }}</div>
                        </div>
                        <div class="flex flex-row items-center gap-2">
                            <Shield :size="18" class="text-green-500" />
                            <div>{{ h.stats.defenseDice }}</div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<style scoped></style>
