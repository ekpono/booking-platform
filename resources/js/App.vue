<template>
    <div class="min-h-screen bg-white">
        <nav class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-slate-900">
                            Booking Platform
                        </h1>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button
                            @click="activeTab = 'bookings'"
                            :class="[
                                'rounded-full px-4 py-2 text-sm font-medium',
                                activeTab === 'bookings'
                                    ? 'bg-[#45b2e9] text-white'
                                    : 'text-slate-500 hover:text-slate-900'
                            ]"
                        >
                            Bookings
                        </button>
                        <button
                            @click="activeTab = 'clients'"
                            :class="[
                                'rounded-full px-4 py-2 text-sm font-medium',
                                activeTab === 'clients'
                                    ? 'bg-[#45b2e9] text-white'
                                    : 'text-slate-500 hover:text-slate-900'
                            ]"
                        >
                            Clients
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
            <BookingList v-if="activeTab === 'bookings'" />
            <ClientList v-if="activeTab === 'clients'" />
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import BookingList from './components/BookingList.vue';
import ClientList from './components/ClientList.vue';

const activeTab = ref('bookings');
</script>
