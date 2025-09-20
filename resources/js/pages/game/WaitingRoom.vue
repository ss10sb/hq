<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { Game, Player } from '@/types/game';
import { computed, onMounted, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useEcho, useEchoPresence } from '@laravel/echo-vue';
import { Button } from '@/components/ui/button';
import PlayGameController from '@/actions/App/Http/Controllers/Game/PlayGameController';
import { Board } from '@/types/board';
import { Hero } from '@/types/hero';

const page = usePage();

const props = defineProps<{
    game: Game;
    board: Board;
}>();

const currentUserId = computed<number | null>(() => page.props.auth.user?.id ?? null);
const isGameMaster = computed(() => props.game.gameMasterId === currentUserId.value);

// Local reactive players state, initialized from server props
const roomPlayers = ref<Player[]>([...props.game.players]);

type ExtendedPlayer = Player & { isYou: boolean; isGM: boolean; heroes: Hero[]; };
// Build a derived list of players with display-friendly hero text and a flag for the current user
const players = computed(() => {
    return roomPlayers.value.map((p: Player) => {
        const isYou = currentUserId.value !== null && p.id === currentUserId.value;
        const isGM = p.id === props.game.gameMasterId;
        const heroes = getPlayersHeroes(p);
        return {
            ...p,
            isYou,
            isGM,
            heroes
        } as ExtendedPlayer;
    });
});
const isPresentInRoom = (player: Player) => playersPresent.value.some(p => p.id === player.id);

const getPlayersHeroes = (player: Player | ExtendedPlayer) => {
    const heroes = [];
    for (const hero of props.game.heroes) {
        if (hero.playerId === player.id) {
            heroes.push(hero);
        }
    }
    return heroes;
};

const playersPresent = ref<Player[]>([]);

// Track whether a reload is already in progress to avoid overlapping reloads
const reloadingPlayers = ref(false);
const refreshRoomPlayers = (): void => {
    if (reloadingPlayers.value) {
        return;
    }
    reloadingPlayers.value = true;

    router.reload({
        only: ['game'],
        preserveScroll: true,
        onSuccess: (pageData) => {
            try {
                const updatedGame = (pageData.props as any).game as Game;
                if (updatedGame?.players) {
                    roomPlayers.value = [...updatedGame.players];
                }
            } finally {
                reloadingPlayers.value = false;
            }
        },
        onError: () => {
            reloadingPlayers.value = false;
        },
        onFinish: () => {
            // Safety net to ensure flag is reset
            reloadingPlayers.value = false;
        }
    });
};

const gameReady = ref(false);
// Private channel to indicate that the game is ready for play
useEcho(
    `game-access.${props.game.id}`,
    '.game.started',
    (e) => {
        gameReady.value = true;
    }
);

const { channel } = useEchoPresence(
    `game.waiting-room.${props.game.id}`,
    [],
    () => {
    }
);
onMounted(() => {
    const presenceChannel = channel();
    const ensureSelfPresent = () => {
        const myId = currentUserId.value;
        if (myId == null) {
            return;
        }
        if (!playersPresent.value.some(u => u.id === myId)) {
            // Add a minimal self user if missing (name is optional for indicator)
            const selfName = (page.props.auth.user?.name as string | undefined) ?? 'You';
            playersPresent.value.push({ id: myId, name: selfName });
        }
    };

    presenceChannel.here((users: Player[]) => {
        console.log('Users in the room:', users);
        // Replace list with a fresh copy to avoid referencing Echo's internal array
        playersPresent.value = [...users];
        ensureSelfPresent();
    });

    presenceChannel.joining((user: Player) => {
        console.log('User joined:', user);
        if (!playersPresent.value.find(p => p.id === user.id)) {
            playersPresent.value.push(user);
        }
        if (!roomPlayers.value.find(p => p.id === user.id)) {
            // Fetch updated players from the server since this user was not in the initial list
            refreshRoomPlayers();
        }
        ensureSelfPresent();
    });

    presenceChannel.leaving((user: Player) => {
        console.log('User left:', user);
        const myId = currentUserId.value;
        if (myId != null && user.id === myId) {
            // Occasionally Echo may emit a self-leave during reconnection; ignore to keep indicator
            console.warn('Ignoring self leave event to preserve presence indicator.');
            return;
        }
        const index = playersPresent.value.findIndex(p => p.id === user.id);
        if (index !== -1) {
            playersPresent.value.splice(index, 1);
        }
    });
});
</script>

<template>
    <Head title="Waiting Room" />
    <AppHeaderLayout>
        <Card class="my-5">
            <CardHeader class="text-center">
                <CardTitle class="text-xl">Waiting Room</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col items-center gap-3">
                        <div class="flex flex-row items-center gap-2">
                            <h1 class="text-2xl font-bold">{{ board.name }}</h1>
                            <span>{{ board.group }} ({{ board.order }})</span>
                        </div>

                        <h2 class="text-lg font-bold">Join Key: {{ game.joinKey }}</h2>
                        <div v-if="isGameMaster">
                            <Link :href="PlayGameController(game.id)">
                                <Button>Start Game</Button>
                            </Link>
                        </div>
                        <div v-if="gameReady">
                            <Link :href="PlayGameController(game.id)">
                                <Button variant="success">Play Game</Button>
                            </Link>
                        </div>
                    </div>

                    <!-- Players List -->
                    <div class="mt-2">
                        <div class="flex flex-row items-center gap-2 mb-2">
                            <h2 class="text-lg font-semibold">Heroes</h2>
                            <span>{{ players.filter((p) => !p.isGM).length }} / {{ game.maxHeroes }}</span>
                        </div>
                        <div v-if="players.length" class="divide-y">
                            <div v-for="p in players" :key="p.id" class="w-full py-2">
                                <div class="flex flex-col gap-2 border-b border-gray-200 px-2 py-1.5 dark:border-gray-700">
                                    <div class="flex flex-row justify-between items-center gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">{{ p.name }}</span>
                                            <span v-if="p.isYou"
                                                  class="rounded bg-primary/10 px-2 py-0.5 text-xs text-primary">You</span>
                                            <span v-if="p.isGM"
                                                  class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">GM</span>
                                        </div>
                                        <!-- indicates if user is present in room -->
                                        <div v-if="isPresentInRoom(p)" class="flex items-center gap-2">
                                            <span class="inline-block h-3 w-3 rounded-full bg-green-500"
                                                  aria-label="Present"></span>
                                        </div>
                                        <div v-else>
                                            <span class="inline-block h-3 w-3 rounded-full bg-gray-400"
                                                  aria-label="Not present"></span>
                                        </div>
                                    </div>
                                    <div class="text-sm ms-5 mb-2">
                                        <template v-if="p.heroes.length">
                                            <div class="flex flex-row flex-wrap gap-5">
                                                <div v-for="hero in p.heroes" :key="hero.id">
                                                    {{ hero.name }}
                                                    <span v-if="hero.type" class="text-muted-foreground">({{ hero.type }})</span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-sm text-muted-foreground">No players joined yet.</div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppHeaderLayout>
</template>

<style scoped></style>
