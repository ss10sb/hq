<script setup lang="ts">
import { Board } from '@/types/board';
import { LengthAwarePaginator } from '@/types/pagination';
import LengthAwarePagination from '@/components/ui/pagination/LengthAwarePagination.vue';

defineProps<{
    boards: LengthAwarePaginator<Board>;
}>();
</script>

<template>
    <div v-if="boards.data.length" class="@container">
        <div v-for="board in boards.data" :key="board.id">{{ board.name }}</div>
        <LengthAwarePagination :paginator="boards" />
    </div>
    <div v-else>No boards found.</div>
</template>

<style scoped></style>
