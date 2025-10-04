<script setup lang="ts">
import joinGameController from '@/actions/App/Http/Controllers/Game/JoinGameController';
import SelectHeroController from '@/actions/App/Http/Controllers/Hero/SelectHeroController';
import Errors from '@/components/Errors.vue';
import HeroForm from '@/components/hero/HeroForm.vue';
import HeroesList from '@/components/hero/HeroesList.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { cloneHero, defaultsFor, heroArchetypes } from '@/lib/board/hero';
import type { BreadcrumbItem } from '@/types';
import { Hero, HeroArchetype, type NewHero } from '@/types/hero';
import type { LengthAwarePaginator } from '@/types/pagination';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Select Hero',
        href: SelectHeroController().url,
    },
];

const props = defineProps<{
    myHeroes: LengthAwarePaginator<Hero>;
    heroes: NewHero[];
}>();

type Mode = 'idle' | 'create' | 'edit';
const mode = ref<Mode>('idle');
const selected = ref<NewHero | null>(null);
const selectedId = ref<number | null>(null);
const form = useForm<NewHero>({
    name: '',
    type: HeroArchetype.Berserker,
    stats: {
        bodyPoints: 0,
        mindPoints: 0,
        attackDice: 0,
        defenseDice: 0,
        currentBodyPoints: 0,
    },
    inventory: [],
    equipment: [],
    gold: 0,
});

const archetypes = computed<HeroArchetype[]>(() => heroArchetypes(props.heroes));

const heroDraft = ref<NewHero>({
    name: '',
    type: HeroArchetype.Berserker,
    stats: { bodyPoints: 0, mindPoints: 0, attackDice: 0, defenseDice: 0, currentBodyPoints: 0 },
    inventory: [],
    equipment: [],
    gold: 0,
});

function startCreate() {
    mode.value = 'create';
    selected.value = null;
    selectedId.value = null;
    heroDraft.value = defaultsFor(archetypes.value[0] ?? HeroArchetype.Berserker, props.heroes);
}

function selectHero(hero: Hero) {
    mode.value = 'edit';
    selectedId.value = hero.id;
    selected.value = cloneHero(hero);
    heroDraft.value = cloneHero(hero);
}

function onTypeChange(newType: HeroArchetype) {
    if (mode.value === 'create') {
        const newHero = defaultsFor(newType, props.heroes);
        heroDraft.value = { ...heroDraft.value, type: newHero.type,  stats: newHero.stats};
    } else {
        heroDraft.value = { ...heroDraft.value, type: newType };
    }
}

function resetToIdle() {
    mode.value = 'idle';
    selected.value = null;
    selectedId.value = null;
    heroDraft.value = {
        name: '',
        type: HeroArchetype.Berserker,
        stats: { bodyPoints: 0, mindPoints: 0, attackDice: 0, defenseDice: 0, currentBodyPoints: 0 },
        inventory: [],
        equipment: [],
        gold: 0,
    };
    form.reset();
}

const submitForm = async () => {
    const cleaned: NewHero = {
        ...heroDraft.value,
        inventory: heroDraft.value.inventory.filter((it) => it.name && it.name.trim().length > 0),
        equipment: heroDraft.value.equipment.filter((eq) => eq.name && eq.name.trim().length > 0),
    };
    Object.assign(form, cleaned);
    if (selectedId.value) {
        await form.submit(SelectHeroController.update(selectedId.value), {
            onSuccess: () => {
                resetToIdle();
            },
        });
    } else {
        await form.submit(SelectHeroController.create(), {
            onSuccess: () => {
                resetToIdle();
            },
        });
    }
};

const joinGameSession = (heroId: number) => {
    router.get(joinGameController({ heroId, joinKey: joinKey.value }).url);
};
const joinKey = ref('');
</script>

<template>
    <Head title="Select Hero" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Errors />
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex flex-row items-center justify-between gap-4">
                <Button @click="startCreate">Create New Hero</Button>
                <div v-if="selectedId && selected">
                    <span>Join a game with {{ selected.name }} </span>
                    <form class="flex flex-row" @submit.prevent="joinGameSession(selectedId)">
                        <input
                            type="text"
                            name="join_key"
                            v-model="joinKey"
                            placeholder="Enter game join key"
                            aria-label="Game Join Key"
                            class="rounded-tl-sm rounded-tr-none rounded-br-none rounded-bl-sm border border-[#19140035] bg-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] outline-none focus:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:focus:border-[#62605b]"
                        />
                        <Button variant="outline" type="submit" :disabled="!joinKey" class="rounded-tl-none rounded-bl-none border"> Join </Button>
                    </form>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader class="text-center">
                        <CardTitle class="text-xl">My Heroes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <HeroesList :heroes="myHeroes" @select="selectHero" />
                    </CardContent>
                </Card>
                <Card>
                    <CardContent>
                        <template v-if="mode !== 'idle'">
                            <HeroForm
                                :key="`${mode}-${selectedId ?? 'new'}`"
                                v-model="heroDraft"
                                :mode="mode === 'create' ? 'create' : 'edit'"
                                :archetypes="archetypes"
                                :processing="form.processing"
                                @cancel="resetToIdle"
                                @submit="submitForm"
                                @type-change="onTypeChange"
                            />
                        </template>
                        <template v-else> Select a hero or click Create New Hero to create a new hero. </template>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped></style>
