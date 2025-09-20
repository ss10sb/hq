<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AppPageProps } from '@/types';

const page = usePage<AppPageProps>();
</script>

<template>
    <div v-if="page.props.errors.length"
         v-for="error in page.props.errors"
         class="mb-4 text-sm text-red-600">
        {{ error }}
    </div>
</template>

<style scoped>

</style>