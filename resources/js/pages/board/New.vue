<script setup lang="ts">
import NewBoardController from '@/actions/App/Http/Controllers/Board/NewBoardController';
import SelectBoardController from '@/actions/App/Http/Controllers/Board/SelectBoardController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { BoardGroups, NewBoard } from '@/types/board';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Select Board',
        href: SelectBoardController().url,
    },
    {
        title: 'New Board',
        href: NewBoardController().url,
    },
];

const props = defineProps<{
    groups: BoardGroups;
    board: NewBoard;
}>();

const board = props.board;
const groups = props.groups;

const form = useForm<NewBoard>({
    name: board.name,
    group: board.group,
    order: board.order,
    height: board.height,
    width: board.width,
    is_public: board.is_public ?? false,
});

const submitForm = () => {
    form.submit(NewBoardController.store());
};
</script>

<template>
    <Head title="New Board" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <form @submit.prevent="submitForm" class="flex flex-col gap-6 p-4">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        v-model="form.name"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Name of quest"
                    />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="group">Group {{ form.group }}</Label>
                    <Select v-model="form.group" autocomplete="group" required>
                        <SelectTrigger id="group" name="group" class="w-full" :tabindex="2">
                            <SelectValue placeholder="Select a group" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="(group, key) in groups" :key="key" :value="key">
                                {{ group }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.group" />
                </div>
                <div class="grid gap-2">
                    <Label for="order">Quest Number (in group)</Label>
                    <Input id="order" type="number" v-model="form.order" required :tabindex="3" autocomplete="order" name="order" />
                    <InputError :message="form.errors.order" />
                </div>
                <div class="grid gap-2">
                    <Label for="height">Rows (height)</Label>
                    <Input id="height" type="number" v-model="form.height" required :tabindex="5" autocomplete="height" name="height" />
                    <InputError :message="form.errors.height" />
                </div>
                <div class="grid gap-2">
                    <Label for="width">Columns (width)</Label>
                    <Input id="width" type="number" v-model="form.width" required :tabindex="4" autocomplete="width" name="width" />
                    <InputError :message="form.errors.width" />
                </div>
                <div class="grid gap-2">
                    <Label for="is-public">
                        <Checkbox v-model="form.is_public" :tabindex="5" name="is_public" id="is-public" />
                        Public?
                    </Label>
                    <InputError :message="form.errors.is_public" />
                </div>
                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create board
                </Button>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped></style>
