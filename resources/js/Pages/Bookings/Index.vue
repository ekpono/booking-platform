<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BookingList from '@/components/BookingList.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-slate-900">
                Bookings
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <BookingList />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
