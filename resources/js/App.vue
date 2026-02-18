<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Booking Platform
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button
                            @click="activeTab = 'bookings'"
                            :class="[
                                'px-3 py-2 rounded-md text-sm font-medium',
                                activeTab === 'bookings'
                                    ? 'bg-indigo-100 text-indigo-700'
                                    : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            Bookings
                        </button>
                        <button
                            @click="activeTab = 'clients'"
                            :class="[
                                'px-3 py-2 rounded-md text-sm font-medium',
                                activeTab === 'clients'
                                    ? 'bg-indigo-100 text-indigo-700'
                                    : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            Clients
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
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
