<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Bookings</h2>
            <button
                @click="showForm = true"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors"
            >
                New Booking
            </button>
        </div>

        <!-- Week Selector -->
        <div class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Select Week:</label>
                <input
                    type="date"
                    v-model="selectedDate"
                    @change="fetchBookings"
                    class="border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
                <button
                    @click="goToCurrentWeek"
                    class="text-sm text-indigo-600 hover:text-indigo-800"
                >
                    Current Week
                </button>
            </div>
            <p class="mt-2 text-sm text-gray-500">
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
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-500">
                Loading bookings...
            </div>

            <div v-else-if="bookings.length === 0" class="p-8 text-center text-gray-500">
                No bookings found for this week.
            </div>

            <ul v-else class="divide-y divide-gray-200">
                <li
                    v-for="booking in bookings"
                    :key="booking.id"
                    class="p-4 hover:bg-gray-50"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ booking.title }}
                            </h3>
                            <p v-if="booking.description" class="mt-1 text-sm text-gray-600">
                                {{ booking.description }}
                            </p>
                            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
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
                                class="text-indigo-600 hover:text-indigo-800 text-sm"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteBooking(booking.id)"
                                class="text-red-600 hover:text-red-800 text-sm"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import BookingForm from './BookingForm.vue';

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
