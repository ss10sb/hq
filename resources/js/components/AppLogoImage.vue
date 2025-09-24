<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import logoImage from '../../images/logo.jpg';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img :src="logoImage" :class="className" v-bind="$attrs" alt="HQ Logo" />
</template>
