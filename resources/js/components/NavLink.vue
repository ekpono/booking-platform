<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center border-b-2 border-[#45b2e9] px-2 pt-1 text-sm font-semibold text-slate-900'
        : 'inline-flex items-center border-b-2 border-transparent px-2 pt-1 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:border-slate-200',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
