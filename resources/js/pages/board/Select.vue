<script setup lang="ts">
import NewBoardController from '@/actions/App/Http/Controllers/Board/NewBoardController';
import SelectBoardController from '@/actions/App/Http/Controllers/Board/SelectBoardController';
import BoardsList from '@/components/board/BoardsList.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Board } from '@/types/board';
import { Head, Link } from '@inertiajs/vue3';
import { LengthAwarePaginator } from '@/types/pagination';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Select Board',
        href: SelectBoardController().url,
    },
];
defineProps<{
    publicBoards: LengthAwarePaginator<Board>;
    myBoards: LengthAwarePaginator<Board>;
}>();
</script>

<template>
    <Head title="Select Board" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <Link :href="NewBoardController()">
                    <Button>Create New Board</Button>
                </Link>
            </div>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader class="text-center">
                        <CardTitle class="text-xl">Public Boards</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <BoardsList :boards="publicBoards" />
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="text-center">
                        <CardTitle class="text-xl">My Boards</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <BoardsList :boards="myBoards" />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped></style>
