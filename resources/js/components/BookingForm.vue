<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ booking ? 'Edit Booking' : 'New Booking' }}
                </h3>
            </div>

            <form @submit.prevent="saveBooking" class="p-6 space-y-4">
                <!-- Client Select -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Client
                    </label>
                    <select
                        v-model="form.client_id"
                        required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">Select a client</option>
                        <option v-for="client in clients" :key="client.id" :value="client.id">
                            {{ client.name }}
                        </option>
                    </select>
                    <p v-if="errors.client_id" class="mt-1 text-sm text-red-600">
                        {{ errors.client_id[0] }}
                    </p>
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Title
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Meeting title"
                    />
                    <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                        {{ errors.title[0] }}
                    </p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Description (optional)
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Add any notes..."
                    ></textarea>
                </div>

                <!-- Start Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Start Time
                    </label>
                    <input
                        v-model="form.start_time"
                        type="datetime-local"
                        required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <p v-if="errors.start_time" class="mt-1 text-sm text-red-600">
                        {{ errors.start_time[0] }}
                    </p>
                </div>

                <!-- End Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        End Time
                    </label>
                    <input
                        v-model="form.end_time"
                        type="datetime-local"
                        required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <p v-if="errors.end_time" class="mt-1 text-sm text-red-600">
                        {{ errors.end_time[0] }}
                    </p>
                </div>

                <!-- Overlap Error -->
                <div v-if="errors.overlap" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ errors.overlap[0] }}
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : (booking ? 'Update' : 'Create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import { ensureCsrfCookie } from '@/utils/csrf';

const props = defineProps({
    booking: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'saved']);

const clients = ref([]);
const saving = ref(false);
const errors = ref({});

const form = reactive({
    client_id: '',
    title: '',
    description: '',
    start_time: '',
    end_time: ''
});

const resetForm = () => {
    form.client_id = '';
    form.title = '';
    form.description = '';
    form.start_time = '';
    form.end_time = '';
};

const formatDateTimeLocal = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toISOString().slice(0, 16);
};

const formatForApi = (dateTimeLocal) => {
    if (!dateTimeLocal) return '';
    return dateTimeLocal.replace('T', ' ') + ':00';
};

watch(
    () => props.booking,
    (newBooking) => {
        if (newBooking) {
            form.client_id = newBooking.client.id;
            form.title = newBooking.title;
            form.description = newBooking.description || '';
            form.start_time = formatDateTimeLocal(newBooking.start_time);
            form.end_time = formatDateTimeLocal(newBooking.end_time);
        } else {
            resetForm();
        }
    },
    { immediate: true },
);

const fetchClients = async () => {
    try {
        await ensureCsrfCookie();
        const response = await axios.get('/api/clients');
        clients.value = response.data.data;
    } catch (err) {
        console.error('Error fetching clients:', err);
    }
};

const saveBooking = async () => {
    saving.value = true;
    errors.value = {};

    const data = {
        client_id: form.client_id,
        title: form.title,
        description: form.description || null,
        start_time: formatForApi(form.start_time),
        end_time: formatForApi(form.end_time)
    };

    try {
        await ensureCsrfCookie();
        if (props.booking) {
            await axios.put(`/api/bookings/${props.booking.id}`, data);
        } else {
            await axios.post('/api/bookings', data);
            resetForm();
        }
        emit('saved');
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {};
        } else {
            errors.value = { overlap: ['An error occurred. Please try again.'] };
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchClients();
});
</script>
