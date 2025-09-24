<script setup lang="ts">
import SelectHeroController from '@/actions/App/Http/Controllers/Hero/SelectHeroController';
import { Button } from '@/components/ui/button';
import LengthAwarePagination from '@/components/ui/pagination/LengthAwarePagination.vue';
import type { Hero } from '@/types/hero';
import type { LengthAwarePaginator } from '@/types/pagination';
import { Form } from '@inertiajs/vue3';

const emit = defineEmits<{
    (e: 'select', hero: Hero): void;
}>();

defineProps<{
    heroes: LengthAwarePaginator<Hero>;
}>();

const confirmDelete = () => {
    return window.confirm('Are you sure you want to delete this hero?');
};
</script>

<template>
    <div v-if="heroes.data.length" class="@container">
        <div v-for="hero in heroes.data" :key="hero.id" class="flex flex-row items-center justify-between gap-2 py-4">
            <div
                class="flex-1 cursor-pointer rounded px-3 py-2 hover:bg-muted"
                role="button"
                tabindex="0"
                @click="emit('select', hero)"
                @keydown.enter.prevent="emit('select', hero)"
            >
                {{ hero.name }} ({{ hero.type }})
            </div>
            <Form :action="SelectHeroController.destroy(hero.id)" :on-before="() => confirmDelete()">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
        <LengthAwarePagination :paginator="heroes" class="mt-4" />
    </div>
    <div v-else>No heroes found.</div>
</template>

<style scoped></style>
