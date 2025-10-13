<script setup lang="ts">
import ActionsPanel from '@/components/game/ActionsPanel.vue';
import MonstersPanel from '@/components/game/MonstersPanel.vue';
import PlayersList from '@/components/game/PlayersList.vue';
import type { Element } from '@/types/board';
import { Player } from '@/types/game';
import { Hero, Zargon } from '@/types/hero';
import { computed, nextTick, ref, watch } from 'vue';

// Log types mirror PlayGame.vue, but loosened for prop typing simplicity
// kind: 'text' | 'dice'
// For 'dice', we expect { actor: string, diceType: string, count: number, results: Array<{ icon: any, color?: string }> }

const props = defineProps<{
    heroes: (Hero | Zargon)[];
    players: Player[];
    isGameMaster: boolean;
    activeHeroId: number;
    presentIds: number[];
    currentUserId: number | null;
    isSelectActive?: boolean;
    stepsByHero?: Record<number, number>;
    previewStepsByHero?: Record<number, number>;
    canEndTurn?: boolean;
    selectedMonster?: Element | null;
    gameLog?: any[];
}>();

const emit = defineEmits<{
    (e: 'move-up', index: number): void;
    (e: 'move-down', index: number): void;
    (e: 'set-active', heroId: number): void;
    (e: 'update-body', payload: { heroId: number | null; value: number }): void;
    (e: 'roll-dice', payload: { type: any; count: number }): void;
    (e: 'end-turn'): void;
    (e: 'toggle-select'): void;
    (e: 'assign-hero', payload: { heroId: number; playerId: number }): void;
    (e: 'save-hero', payload: { heroId: number; hero: any }): void;
    (e: 'monster-update-body', payload: { elementId: string; value: number }): void;
    (e: 'monster-close'): void;
    (e: 'remove-hero', index: number): void;
}>();

const logContainerRef = ref<HTMLElement | null>(null);
watch(
    () => (props.gameLog || []).length,
    async () => {
        await nextTick();
        const el = logContainerRef.value;
        if (el) {
            el.scrollTop = 0; // keep newest at top in view
        }
    },
);

const activeHeroAttackDice = computed<number>(() => {
    const h = (props.heroes || []).find((x: any) => x && (x as any).id === props.activeHeroId) as any;
    const v = Number(h?.stats?.attackDice ?? 0);
    return Number.isFinite(v) ? v : 0;
});
const diceValue = (entry: any) => {
    if (entry.diceType === 'six_sided') {
        return ' (' + entry.results.reduce((s: number, r: any) => s + r.value, 0) + ')';
    }
    return '';
}
</script>

<template>
    <aside
        class="flex flex-1 flex-col overflow-hidden rounded border border-gray-200 bg-white p-3 dark:border-neutral-800 dark:bg-neutral-900"
    >
        <h3 class="mb-2 font-semibold">Game Panel</h3>
        <!-- Top tools stack -->
        <div class="flex-shrink-0 overflow-auto">
            <!-- Players/Heroes List -->
            <PlayersList
                :heroes="heroes"
                :players="players"
                :active-hero-id="activeHeroId"
                :is-game-master="isGameMaster"
                :present-ids="presentIds"
                :current-user-id="currentUserId"
                :steps-by-hero="props.stepsByHero || {}"
                :preview-steps-by-hero="props.previewStepsByHero || {}"
                @move-up="(idx) => emit('move-up', idx)"
                @move-down="(idx) => emit('move-down', idx)"
                @set-active="(heroId) => emit('set-active', heroId)"
                @update-body="(payload) => emit('update-body', payload)"
                @save-hero="(payload) => emit('save-hero', payload)"
                @assign-hero="(payload) => emit('assign-hero', payload)"
                @remove-hero="(idx) => emit('remove-hero', idx)"
            />
            <!-- Monsters Panel -->
            <MonstersPanel
                :selected-monster="props.selectedMonster ?? null"
                :is-game-master="isGameMaster"
                @update-body="(payload) => emit('monster-update-body', payload)"
                @close="() => emit('monster-close')"
            />
            <!-- ActionsPanel -->
            <ActionsPanel
                :is-select-active="isSelectActive"
                :current-user-id="currentUserId"
                :can-end-turn="props.canEndTurn === true"
                :active-hero-attack-dice="activeHeroAttackDice"
                @toggle-select="() => emit('toggle-select')"
                @end-turn="() => emit('end-turn')"
                @roll-dice="(payload) => emit('roll-dice', payload)"
            />
        </div>

        <!-- Game Log at the bottom, occupies ~1/3 and flexes -->
        <div
            ref="logContainerRef"
            class="mt-2 min-h-0 flex-1 basis-1/3 overflow-auto rounded border border-gray-200 bg-white p-2 dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div class="flex flex-col gap-2">
                <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Game Log</h3>
                <div v-if="!props.gameLog || props.gameLog.length === 0" class="text-sm text-neutral-500">No activity yet.</div>
                <ul v-else class="flex flex-col gap-1 text-sm">
                    <li v-for="(entry, idx) in props.gameLog || []" :key="idx" class="text-neutral-800 dark:text-neutral-200">
                        <template v-if="entry.kind === 'text'"> • {{ (entry as any).text }} </template>
                        <template v-else>
                            <div class="flex flex-col gap-1">
                                <div>
                                    • {{ (entry as any).actor }} rolled {{ (entry as any).count }}
                                    {{ (entry as any).diceType === 'six_sided' ? 'six-sided' : 'combat' }}
                                    {{ (entry as any).count === 1 ? 'die' : 'dice' }}{{ diceValue(entry) }}:
                                </div>
                                <div class="flex flex-row flex-wrap items-center gap-2 pl-3">
                                    <span v-for="(r, i) in (entry as any).results" :key="i" class="inline-flex items-center">
                                        <component :is="(r as any).icon" class="size-6" :style="{ color: (r as any).color || undefined }" />
                                    </span>
                                </div>
                            </div>
                        </template>
                    </li>
                </ul>
            </div>
        </div>
    </aside>
</template>

<style scoped></style>
