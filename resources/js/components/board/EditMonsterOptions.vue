<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { MonsterType } from '@/types/board';
import type { Stats } from '@/types/gameplay';
import { computed } from 'vue';

const props = defineProps<{
    type: MonsterType;
    customText: string;
    stats: Stats;
}>();

const emit = defineEmits<{
    (e: 'update:type', value: MonsterType): void;
    (e: 'update:customText', value: string): void;
    (e: 'update:stats', value: Stats): void;
}>();

const modelType = computed<MonsterType>({
    get() {
        return props.type;
    },
    set(val: MonsterType) {
        emit('update:type', val);
    },
});

const modelCustomText = computed<string>({
    get() {
        return props.customText;
    },
    set(val: string) {
        emit('update:customText', val);
    },
});

// For stats, emit a full object when any field changes
function updateStat<K extends keyof Stats>(key: K, value: number): void {
    const next: Stats = { ...props.stats, [key]: value } as Stats;
    emit('update:stats', next);
}

// Computed proxies for v-model on Input component
const bodyPoints = computed<number>({
    get() {
        return props.stats?.bodyPoints ?? 0;
    },
    set(val: number) {
        updateStat('bodyPoints', Number(val));
    },
});

const mindPoints = computed<number>({
    get() {
        return props.stats?.mindPoints ?? 0;
    },
    set(val: number) {
        updateStat('mindPoints', Number(val));
    },
});

const attackDice = computed<number>({
    get() {
        return props.stats?.attackDice ?? 0;
    },
    set(val: number) {
        updateStat('attackDice', Number(val));
    },
});

const defenseDice = computed<number>({
    get() {
        return props.stats?.defenseDice ?? 0;
    },
    set(val: number) {
        updateStat('defenseDice', Number(val));
    },
});

const currentBodyPoints = computed<number>({
    get() {
        return props.stats?.currentBodyPoints ?? 0;
    },
    set(val: number) {
        updateStat('currentBodyPoints', Number(val));
    },
});
</script>

<template>
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">Monster</h2>
        <div class="grid gap-2 my-3">
            <Label for="monster-type">Type</Label>
            <Select v-model="(modelType as any)">
                <SelectTrigger id="monster-type" class="w-full">
                    <SelectValue placeholder="Select a monster type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="MonsterType.Goblin">Goblin</SelectItem>
                    <SelectItem :value="MonsterType.Orc">Orc</SelectItem>
                    <SelectItem :value="MonsterType.Skeleton">Skeleton</SelectItem>
                    <SelectItem :value="MonsterType.Custom">Custom</SelectItem>
                </SelectContent>
            </Select>

            <div v-if="modelType === MonsterType.Custom" class="grid gap-2 mt-3">
                <Label for="monster-custom">Custom name</Label>
                <Input id="monster-custom" type="text" v-model="modelCustomText" placeholder="e.g. Chaos Warrior" />
            </div>

            <div class="grid gap-2 mt-4">
                <h3 class="text-xs font-semibold text-neutral-600 dark:text-neutral-300">Stats</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <Label for="bp">Body Points</Label>
                        <NumberField v-model="(bodyPoints as any)" :min="0">
                            <NumberFieldContent>
                                <NumberFieldDecrement />
                                <NumberFieldInput />
                                <NumberFieldIncrement />
                            </NumberFieldContent>
                        </NumberField>
                    </div>
                    <div>
                        <Label for="mp">Mind Points</Label>
                        <NumberField v-model="(mindPoints as any)" :min="0">
                            <NumberFieldContent>
                                <NumberFieldDecrement />
                                <NumberFieldInput />
                                <NumberFieldIncrement />
                            </NumberFieldContent>
                        </NumberField>
                    </div>
                    <div>
                        <Label for="ad">Attack Dice</Label>
                        <NumberField v-model="(attackDice as any)" :min="0">
                            <NumberFieldContent>
                                <NumberFieldDecrement />
                                <NumberFieldInput />
                                <NumberFieldIncrement />
                            </NumberFieldContent>
                        </NumberField>
                    </div>
                    <div>
                        <Label for="dd">Defense Dice</Label>
                        <NumberField v-model="(defenseDice as any)" :min="0">
                            <NumberFieldContent>
                                <NumberFieldDecrement />
                                <NumberFieldInput />
                                <NumberFieldIncrement />
                            </NumberFieldContent>
                        </NumberField>
                    </div>
                    <div class="col-span-2">
                        <Label for="cbp">Current Body Points</Label>
                        <NumberField v-model="(currentBodyPoints as any)" :min="0" :max="props.stats?.bodyPoints ?? 0">
                            <NumberFieldContent>
                                <NumberFieldDecrement />
                                <NumberFieldInput />
                                <NumberFieldIncrement />
                            </NumberFieldContent>
                        </NumberField>
                    </div>
                </div>
            </div>

            <p class="text-xs text-neutral-500">Monsters can be placed on Floor or Fixture tiles and start hidden.</p>
        </div>
    </div>
</template>

<style scoped></style>
