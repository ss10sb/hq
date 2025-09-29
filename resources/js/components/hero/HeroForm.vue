<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { EquipmentItem, HeroArchetype, InventoryItem, NewHero } from '@/types/hero';
import { computed } from 'vue';

const props = defineProps<{
    modelValue: NewHero;
    mode: 'create' | 'edit';
    archetypes: HeroArchetype[];
    processing?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: NewHero): void;
    (e: 'submit'): void;
    (e: 'cancel'): void;
    (e: 'type-change', value: HeroArchetype): void;
}>();

// Proxy for Select v-model to properly reflect current archetype and notify parent on change
const typeProxy = computed<string>({
    get() {
        return props.modelValue.type as unknown as string;
    },
    set(val: string) {
        emit('type-change', val as HeroArchetype);
    },
});

function update<K extends keyof NewHero>(key: K, value: NewHero[K]) {
    emit('update:modelValue', { ...props.modelValue, [key]: value } as NewHero);
}

function updateStat(key: keyof NewHero['stats'], value: number) {
    emit('update:modelValue', {
        ...props.modelValue,
        stats: {
            ...props.modelValue.stats,
            [key]: value,
        },
    });
}

function addInventoryItem(): void {
    const next: InventoryItem = { name: '', description: '', quantity: 1 };
    update('inventory', [...props.modelValue.inventory, next]);
}

function removeInventoryItem(index: number): void {
    const arr = [...props.modelValue.inventory];
    arr.splice(index, 1);
    update('inventory', arr);
}

function updateInventoryItem<K extends keyof InventoryItem>(index: number, key: K, value: InventoryItem[K]): void {
    const arr = props.modelValue.inventory.map((it, i) => (i === index ? { ...it, [key]: value } : it));
    update('inventory', arr);
}

function addEquipmentItem(): void {
    const next: EquipmentItem = { name: '', description: '', attackDice: 0, defenseDice: 0 };
    update('equipment', [...props.modelValue.equipment, next]);
}

function removeEquipmentItem(index: number): void {
    const arr = [...props.modelValue.equipment];
    arr.splice(index, 1);
    update('equipment', arr);
}

function updateEquipmentItem<K extends keyof EquipmentItem>(index: number, key: K, value: EquipmentItem[K]): void {
    const arr = props.modelValue.equipment.map((it, i) => (i === index ? { ...it, [key]: value } : it));
    update('equipment', arr);
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <CardTitle class="text-xl">{{ mode === 'create' ? 'Create Hero' : 'Edit Hero' }}</CardTitle>
            <div class="text-sm text-muted-foreground">Mode: {{ mode }}</div>
        </div>

        <form @submit.prevent="emit('submit')" class="space-y-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Name</label>
                    <Input :model-value="modelValue.name" @update:model-value="(v) => update('name', v as string)" placeholder="Enter hero name" />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium">Archetype</label>
                    <Select v-model="typeProxy">
                        <SelectTrigger>
                            <SelectValue placeholder="Select an archetype" />
                        </SelectTrigger>
                        <SelectContent>
                            <template v-for="type in archetypes" :key="type">
                                <SelectItem :value="String(type)">{{ type }}</SelectItem>
                            </template>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Body Points</label>
                    <NumberField :model-value="modelValue.stats.bodyPoints" :min="0" @update:model-value="(v) => updateStat('bodyPoints', Number(v))">
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Mind Points</label>
                    <NumberField :model-value="modelValue.stats.mindPoints" :min="0" @update:model-value="(v) => updateStat('mindPoints', Number(v))">
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Attack Dice</label>
                    <NumberField :model-value="modelValue.stats.attackDice" :min="0" @update:model-value="(v) => updateStat('attackDice', Number(v))">
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Defense Dice</label>
                    <NumberField
                        :model-value="modelValue.stats.defenseDice"
                        :min="0"
                        @update:model-value="(v) => updateStat('defenseDice', Number(v))"
                    >
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Current Body Points</label>
                    <NumberField
                        :model-value="modelValue.stats.currentBodyPoints"
                        :min="0"
                        :max="modelValue.stats.bodyPoints"
                        @update:model-value="(v) => updateStat('currentBodyPoints', Number(v))"
                    >
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Gold</label>
                    <NumberField
                        :model-value="modelValue.gold"
                        :min="0"
                        @update:model-value="(v) => update('gold', Number(v))"
                    >
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                </div>
            </div>

            <!-- Inventory Management -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <CardTitle class="text-base">Inventory</CardTitle>
                    <Button type="button" size="sm" variant="secondary" @click="addInventoryItem">Add Item</Button>
                </div>
                <div v-if="modelValue.inventory.length" class="space-y-3">
                    <div v-for="(item, idx) in modelValue.inventory" :key="idx" class="rounded border p-3">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                            <div class="md:col-span-3">
                                <label class="text-sm font-medium">Name</label>
                                <Input
                                    :model-value="item.name"
                                    @update:model-value="(v) => updateInventoryItem(idx, 'name', v as string)"
                                    placeholder="Item name"
                                />
                            </div>
                            <div class="md:col-span-7">
                                <label class="text-sm font-medium">Description</label>
                                <Input
                                    :model-value="item.description"
                                    @update:model-value="(v) => updateInventoryItem(idx, 'description', v as string)"
                                    placeholder="Item description"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium">Quantity</label>
                                <NumberField
                                    :model-value="item.quantity"
                                    :min="0"
                                    @update:model-value="(v) => updateInventoryItem(idx, 'quantity', Number(v))"
                                >
                                    <NumberFieldContent>
                                        <NumberFieldDecrement />
                                        <NumberFieldInput />
                                        <NumberFieldIncrement />
                                    </NumberFieldContent>
                                </NumberField>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-end">
                            <Button type="button" size="sm" variant="destructive" @click="removeInventoryItem(idx)">Remove</Button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-sm text-muted-foreground">No inventory items. Click Add Item to start.</div>
            </div>

            <!-- Equipment Management -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <CardTitle class="text-base">Equipment</CardTitle>
                    <Button type="button" size="sm" variant="secondary" @click="addEquipmentItem">Add Equipment</Button>
                </div>
                <div v-if="modelValue.equipment.length" class="space-y-3">
                    <div v-for="(eq, idx) in modelValue.equipment" :key="idx" class="rounded border p-3">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                            <div class="md:col-span-3">
                                <label class="text-sm font-medium">Name</label>
                                <Input
                                    :model-value="eq.name"
                                    @update:model-value="(v) => updateEquipmentItem(idx, 'name', v as string)"
                                    placeholder="Equipment name"
                                />
                            </div>
                            <div class="md:col-span-5">
                                <label class="text-sm font-medium">Description</label>
                                <Input
                                    :model-value="eq.description"
                                    @update:model-value="(v) => updateEquipmentItem(idx, 'description', v as string)"
                                    placeholder="Equipment description"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium">Attack Dice</label>
                                <NumberField
                                    :model-value="eq.attackDice"
                                    :min="0"
                                    @update:model-value="(v) => updateEquipmentItem(idx, 'attackDice', Number(v))"
                                >
                                    <NumberFieldContent>
                                        <NumberFieldDecrement />
                                        <NumberFieldInput />
                                        <NumberFieldIncrement />
                                    </NumberFieldContent>
                                </NumberField>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium">Defense Dice</label>
                                <NumberField
                                    :model-value="eq.defenseDice"
                                    :min="0"
                                    @update:model-value="(v) => updateEquipmentItem(idx, 'defenseDice', Number(v))"
                                >
                                    <NumberFieldContent>
                                        <NumberFieldDecrement />
                                        <NumberFieldInput />
                                        <NumberFieldIncrement />
                                    </NumberFieldContent>
                                </NumberField>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-end">
                            <Button type="button" size="sm" variant="destructive" @click="removeEquipmentItem(idx)">Remove</Button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-sm text-muted-foreground">No equipment added. Click Add Equipment to start.</div>
            </div>

            <div class="flex flex-row items-center justify-between gap-2">
                <Button type="button" variant="secondary" @click="emit('cancel')">Cancel</Button>
                <Button type="submit" :disabled="processing">
                    <template v-if="mode === 'create'">Create Hero</template>
                    <template v-else>Save Changes</template>
                </Button>
            </div>
        </form>
    </div>
</template>

<style scoped></style>
