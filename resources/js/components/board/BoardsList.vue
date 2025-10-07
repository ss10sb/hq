<script setup lang="ts">
import DeleteBoardController from '@/actions/App/Http/Controllers/Board/DeleteBoardController';
import EditBoardController from '@/actions/App/Http/Controllers/Board/EditBoardController';
import { Button } from '@/components/ui/button';
import LengthAwarePagination from '@/components/ui/pagination/LengthAwarePagination.vue';
import { Board } from '@/types/board';
import { LengthAwarePaginator } from '@/types/pagination';
import { Form, Link } from '@inertiajs/vue3';

defineProps<{
    boards: LengthAwarePaginator<Board>;
    showDelete: boolean;
}>();
const confirmDelete = () => {
    return window.confirm('Are you sure you want to delete this board?');
};
</script>

<template>
    <div v-if="boards.data.length" class="@container">
        <div v-for="board in boards.data" :key="board.id" class="flex flex-row items-center justify-between gap-2 py-4">
            <Link :href="EditBoardController(board.id)" class="flex-1 cursor-pointer rounded px-3 py-2 hover:bg-muted">
                {{ board.name }} ({{ board.group }})
            </Link>
            <Form v-if="showDelete" :action="DeleteBoardController(board.id)" :on-before="() => confirmDelete()">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
        <LengthAwarePagination :paginator="boards" />
    </div>
    <div v-else>No boards found.</div>
</template>

<style scoped></style>
