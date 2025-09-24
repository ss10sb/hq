<script setup lang="ts">
import { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const page = usePage<AppPageProps>();

const success = ref<string | null>((page.props.flash?.success as any) ?? null);
const error = ref<string | null>((page.props.flash?.error as any) ?? null);
const info = ref<string | null>((page.props.flash?.info as any) ?? null);

let timers: Array<number | undefined> = [];

function clearTimers(): void {
    for (const t of timers) {
        if (t != null) {
            window.clearTimeout(t);
        }
    }
    timers = [];
}

function startHideTimers(): void {
    clearTimers();
    const ms = 5000; // 5 seconds
    if (success.value) {
        timers.push(window.setTimeout(() => (success.value = null), ms));
    }
    if (error.value) {
        timers.push(window.setTimeout(() => (error.value = null), ms));
    }
    if (info.value) {
        timers.push(window.setTimeout(() => (info.value = null), ms));
    }
}

onMounted(() => {
    startHideTimers();
});

watch(
    () => page.props.flash,
    (f: any) => {
        success.value = (f?.success as any) ?? null;
        error.value = (f?.error as any) ?? null;
        info.value = (f?.info as any) ?? null;
        startHideTimers();
    },
    { deep: true },
);

onBeforeUnmount(() => {
    clearTimers();
});
</script>

<template>
    <div v-if="success" class="mb-4">
        <div class="rounded-lg bg-green-500 p-4 text-white">
            {{ success }}
        </div>
    </div>
    <div v-if="error" class="mb-4">
        <div class="rounded-lg bg-red-500 p-4 text-white">
            {{ error }}
        </div>
    </div>
    <div v-if="info" class="mb-4">
        <div class="rounded-lg bg-blue-500 p-4 text-white">
            {{ info }}
        </div>
    </div>
</template>

<style scoped></style>
