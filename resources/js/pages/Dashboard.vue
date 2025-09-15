<script setup lang="ts">
import selectBoardController from '@/actions/App/Http/Controllers/Board/SelectBoardController';
import joinGameController from '@/actions/App/Http/Controllers/Game/JoinGameController';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
const joinGameSession = () => {
    joinGameController(gameSessionId.value);
};

const gameSessionId = ref('');
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
                            <Button variant="outline" class="w-full"> New Game </Button>
                        </Link>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-3">
                        <h1 class="mb-1 font-medium">Join a game</h1>
                        <p class="mb-3 text-orange-200">Enter a session ID to join an existing game.</p>
                        <form class="flex flex-row" @submit.prevent="joinGameSession">
                            <input
                                type="text"
                                name="session_id"
                                v-model="gameSessionId"
                                placeholder="Enter game session ID"
                                aria-label="Game Session ID"
                                class="rounded-tl-sm rounded-tr-none rounded-br-none rounded-bl-sm border border-[#19140035] bg-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] outline-none focus:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:focus:border-[#62605b]"
                            />
                            <Button variant="outline" type="submit" :disabled="!gameSessionId" class="rounded-tl-none rounded-bl-none border">
                                Join
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
