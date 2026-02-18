<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-white pt-8 sm:justify-center"
    >
        <div class="mb-6">
            <Link href="/">
                <ApplicationLogo />
            </Link>
        </div>

        <div
            class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white px-8 py-6 shadow-sm sm:max-w-md"
        >
            <slot />
        </div>
    </div>
</template>
