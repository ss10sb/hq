<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { TrapType } from '@/types/board';
import { computed } from 'vue';

const props = defineProps<{
    type: TrapType;
    customText: string;
}>();

const emit = defineEmits<{
    (e: 'update:type', value: TrapType): void;
    (e: 'update:customText', value: string): void;
}>();

const modelType = computed<TrapType>({
    get() {
        return props.type;
    },
    set(val: TrapType) {
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
</script>

<template>
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">Trap Type</h2>
        <div class="grid gap-2 my-3">
            <Label for="trap-type">Type</Label>
            <Select v-model="(modelType as any)">
                <SelectTrigger id="trap-type" class="w-full">
                    <SelectValue placeholder="Select a trap type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="TrapType.Pit">Pit</SelectItem>
                    <SelectItem :value="TrapType.Spear">Spear</SelectItem>
                    <SelectItem :value="TrapType.Block">Falling block</SelectItem>
                    <SelectItem :value="TrapType.Custom">Custom</SelectItem>
                </SelectContent>
            </Select>

            <div v-if="modelType === TrapType.Custom" class="grid gap-2 mt-3">
                <Label for="trap-custom">Custom label</Label>
                <Input id="trap-custom" type="text" v-model="modelCustomText" placeholder="e.g. Gas trap" />
            </div>

            <p class="text-xs text-neutral-500">Traps can be placed on Floor or Fixture tiles and start hidden.</p>
        </div>
    </div>
</template>

<style scoped></style>
