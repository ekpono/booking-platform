<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">Bookings</h2>
            <button
                @click="showForm = true"
                class="rounded-full bg-[#45b2e9] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#3794c0]"
            >
                New Booking
            </button>
        </div>

        <!-- Week Selector -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-center gap-4">
                <label class="text-sm font-medium text-slate-700">Select Week:</label>
                <input
                    type="date"
                    v-model="selectedDate"
                    @change="fetchBookings"
                    class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-[#45b2e9] focus:ring-[#45b2e9]"
                />
                <button
                    @click="goToCurrentWeek"
                    class="text-sm font-medium text-[#45b2e9] hover:text-[#2f85b0]"
                >
                    Current Week
                </button>
            </div>
            <p class="mt-2 text-sm text-slate-500">
                Showing bookings for: {{ weekRange }}
            </p>
        </div>

        <!-- Booking Form Modal -->
        <BookingForm
            v-if="showForm"
            :booking="editingBooking"
            @close="closeForm"
            @saved="onBookingSaved"
        />

        <!-- Bookings List -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div v-if="loading" class="p-8 text-center text-slate-500">
                Loading bookings...
            </div>

            <div v-else-if="bookings.length === 0" class="p-8 text-center text-slate-500">
                No bookings found for this week.
            </div>

            <ul v-else class="divide-y divide-gray-200">
                <li
                    v-for="booking in bookings"
                    :key="booking.id"
                    class="p-5 hover:bg-slate-50"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ booking.title }}
                            </h3>
                            <p v-if="booking.description" class="mt-1 text-sm text-slate-600">
                                {{ booking.description }}
                            </p>
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 text-sm text-slate-500">
                                <span>
                                    <strong>Client:</strong> {{ booking.client.name }}
                                </span>
                                <span>
                                    <strong>Time:</strong> {{ formatDateTime(booking.start_time) }} - {{ formatTime(booking.end_time) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button
                                @click="editBooking(booking)"
                                class="text-sm font-medium text-[#45b2e9] hover:text-[#2f85b0]"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteBooking(booking.id)"
                                class="text-sm font-medium text-slate-500 hover:text-slate-900"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import BookingForm from './BookingForm.vue';
import { ensureCsrfCookie } from '@/utils/csrf';

const bookings = ref([]);
const loading = ref(false);
const error = ref(null);
const showForm = ref(false);
const editingBooking = ref(null);
const selectedDate = ref(new Date().toISOString().split('T')[0]);

const weekRange = computed(() => {
    const date = new Date(selectedDate.value);
    const day = date.getDay();
    const diff = date.getDate() - day + (day === 0 ? -6 : 1);
    
    const monday = new Date(date.setDate(diff));
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    
    const options = { month: 'short', day: 'numeric' };
    return `${monday.toLocaleDateString('en-US', options)} - ${sunday.toLocaleDateString('en-US', options)}, ${monday.getFullYear()}`;
});

const fetchBookings = async () => {
    loading.value = true;
    error.value = null;
    
    try {
        await ensureCsrfCookie();
        const response = await axios.get(`/api/bookings?week=${selectedDate.value}`);
        bookings.value = response.data.data;
    } catch (err) {
        error.value = 'Failed to load bookings. Please try again.';
        console.error('Error fetching bookings:', err);
    } finally {
        loading.value = false;
    }
};

const goToCurrentWeek = () => {
    selectedDate.value = new Date().toISOString().split('T')[0];
    fetchBookings();
};

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
};

const formatTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit'
    });
};

const editBooking = (booking) => {
    editingBooking.value = booking;
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingBooking.value = null;
};

const onBookingSaved = () => {
    closeForm();
    fetchBookings();
};

const deleteBooking = async (id) => {
    if (!confirm('Are you sure you want to delete this booking?')) {
        return;
    }
    
    try {
        await ensureCsrfCookie();
        await axios.delete(`/api/bookings/${id}`);
        fetchBookings();
    } catch (err) {
        error.value = 'Failed to delete booking. Please try again.';
        console.error('Error deleting booking:', err);
    }
};

onMounted(() => {
    fetchBookings();
});
</script>
