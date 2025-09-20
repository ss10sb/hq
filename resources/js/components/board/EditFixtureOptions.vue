<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FixtureType } from '@/types/board';
import { computed } from 'vue';
import { useBoardStore } from '@/stores/board';

const props = defineProps<{
    type: FixtureType;
    customText: string;
}>();

const emit = defineEmits<{
    (e: 'update:type', value: FixtureType): void;
    (e: 'update:customText', value: string): void;
}>();

const modelType = computed<FixtureType>({
    get() {
        return props.type;
    },
    set(val: FixtureType) {
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

const boardStore = useBoardStore();
const fixtureOptions = computed(() => {
    // Use catalog from backend; filter out any items that declare themselves as custom
    return (boardStore.fixturesCatalog || []).filter((f: any) => !f?.custom);
});
</script>

<template>
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">Fixture Type</h2>
        <div class="grid gap-2 my-3">
            <Label for="fixture-type">Type</Label>
            <Select v-model="(modelType as any)">
                <SelectTrigger id="fixture-type" class="w-full">
                    <SelectValue placeholder="Select a fixture type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="fx in fixtureOptions" :key="fx.type" :value="fx.type">{{ fx.name }}</SelectItem>
                </SelectContent>
            </Select>

            <div v-if="modelType === FixtureType.Custom" class="grid gap-2 mt-3">
                <Label for="fixture-custom">Custom label</Label>
                <Input id="fixture-custom" type="text" v-model="modelCustomText" placeholder="e.g. Door" />
            </div>

            <p class="text-xs text-neutral-500">Fixtures can only be placed on Floor tiles.</p>
        </div>
    </div>
</template>

<style scoped></style>
