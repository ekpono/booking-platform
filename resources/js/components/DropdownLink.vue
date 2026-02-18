<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-[#45b2e9]"
    >
        <slot />
    </Link>
</template>
