<script setup lang="ts">
import EditBoardController from '@/actions/App/Http/Controllers/Board/EditBoardController';
import SelectBoardController from '@/actions/App/Http/Controllers/Board/SelectBoardController';
import Heading from '@/components/Heading.vue';
import BoardCanvas from '@/components/board/BoardCanvas.vue';
import EditBoardSidebar from '@/components/board/EditBoardSidebar.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Board, BoardGroups, Monster, Trap } from '@/types/board';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    board: Board;
    groups: BoardGroups;
    monsters: Monster[];
    traps: Trap[];
    canEdit: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Select Board',
        href: SelectBoardController().url,
    },
    {
        title: 'Edit Board',
        href: EditBoardController(props.board.id).url,
    },
];
</script>

<template>
    <Head title="Edit Board" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <EditBoardSidebar />
        <div class="flex flex-col gap-4 p-4">
            <Heading :title="board.name" :description="`ID: ${board.id} - ${board.group} (${board.order})`" />
            <BoardCanvas :monsters="monsters" :traps="traps" :board="board" :canEdit="canEdit" />
        </div>
    </AppLayout>
</template>

<style scoped></style>
