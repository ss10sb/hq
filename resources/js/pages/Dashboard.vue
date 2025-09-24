<script setup lang="ts">
import selectBoardController from '@/actions/App/Http/Controllers/Board/SelectBoardController';
import DeleteGameController from '@/actions/App/Http/Controllers/Game/DeleteGameController';
import PlayGameController from '@/actions/App/Http/Controllers/Game/PlayGameController';
import WaitingRoomController from '@/actions/App/Http/Controllers/Game/WaitingRoomController';
import selectHeroController from '@/actions/App/Http/Controllers/Hero/SelectHeroController';
import { Button } from '@/components/ui/button';
import LengthAwarePagination from '@/components/ui/pagination/LengthAwarePagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { statusLabel } from '@/lib/dashboard.lib';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Game, GameStatus } from '@/types/dashboard';
import { LengthAwarePaginator } from '@/types/pagination';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

defineProps<{
    games: LengthAwarePaginator<Game>;
}>();
const page = usePage();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
const confirmDelete = () => {
    return window.confirm('Are you sure you want to delete this board?');
};
const showDelete = (game: Game): boolean => {
    return game.gameMasterId === page.props.auth.user.id;
};
const displayName = (game: Game): string => {
    return `${game.id}: ${game.board.name} (${game.board.group} - ${game.board.order})`;
};
const linkClasses = 'cursor-pointer rounded px-3 py-2 hover:bg-muted';
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-3">
                        <h1 class="mb-1 font-medium">Start a new game</h1>
                        <p class="mb-3 text-orange-200">Select or create a board to start a new game.</p>
                        <Link :href="selectBoardController()">
                            <Button variant="outline" class="w-full"> New Game</Button>
                        </Link>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-3">
                        <h1 class="mb-1 font-medium">Pick your hero</h1>
                        <p class="mb-3 text-orange-200">Create or select a hero.</p>
                        <Link :href="selectHeroController()">
                            <Button variant="outline" class="w-full"> Select Hero</Button>
                        </Link>
                    </div>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="p-3">
                    <h1 class="mb-1 font-medium">My games</h1>
                    <p class="mb-3 text-orange-200">Click the name of an In Progress or Pending game to be taken to the gameboard or waiting room.</p>
                    <div v-if="games.data.length" class="@container">
                        <div v-for="game in games.data" :key="game.id" class="flex flex-row items-center justify-between gap-2 py-4">
                            <Link v-if="game.status === GameStatus.InProgress" :href="PlayGameController(game.id)" :class="linkClasses">
                                {{ displayName(game) }}
                            </Link>
                            <Link v-else-if="game.status === GameStatus.Pending" :href="WaitingRoomController(game.id)" :class="linkClasses"
                                >{{ displayName(game) }}
                            </Link>
                            <div v-else>{{ displayName(game) }}</div>
                            <div>
                                {{ statusLabel(game.status) }}
                            </div>
                            <Form v-if="showDelete(game)" :action="DeleteGameController(game.id)" :on-before="() => confirmDelete()">
                                <Button type="submit" variant="destructive">Delete</Button>
                            </Form>
                        </div>
                        <LengthAwarePagination :paginator="games" />
                    </div>
                    <div v-else>No games found.</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
