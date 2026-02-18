<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-slate-900">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Bookings Card -->
                    <Link
                        :href="route('bookings.index')"
                        class="block overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:shadow-md"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border border-[#45b2e9]/40 bg-[#45b2e9]/10 text-[#45b2e9]">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-slate-900">Bookings</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Manage your bookings and schedule
                                    </p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- Clients Card -->
                    <Link
                        :href="route('clients.index')"
                        class="block overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:shadow-md"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border border-[#45b2e9]/40 bg-[#45b2e9]/10 text-[#45b2e9]">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-slate-900">Clients</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Manage your client list
                                    </p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
