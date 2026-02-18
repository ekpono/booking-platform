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
        ? 'block w-full ps-4 pe-4 py-2 border-l-4 border-[#45b2e9] bg-[#eaf6fd] text-start text-base font-medium text-slate-900'
        : 'block w-full ps-4 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:border-slate-200 hover:bg-slate-50 hover:text-slate-900',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
