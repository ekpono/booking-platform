<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-full border border-[#45b2e9] bg-white px-5 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-[#45b2e9] shadow-sm transition duration-150 ease-in-out hover:bg-[#45b2e9]/10 focus:outline-none focus:ring-2 focus:ring-[#45b2e9] focus:ring-offset-2 disabled:opacity-40"
    >
        <slot />
    </button>
</template>
