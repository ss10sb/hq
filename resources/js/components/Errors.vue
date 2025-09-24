<script setup lang="ts">
import { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';

const page = usePage<AppPageProps>();
</script>

<template v-if="page.props.errors.length">
    <div v-for="(error, idx) in page.props.errors"
         :key="idx"
         class="mb-4 text-sm text-red-600">
        {{ error }}
    </div>
</template>

<style scoped></style>
