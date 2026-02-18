<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Clients</h2>
            <button
                @click="showForm = true"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors"
            >
                New Client
            </button>
        </div>

        <!-- Client Form Modal -->
        <div v-if="showForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        New Client
                    </h3>
                </div>

                <form @submit.prevent="saveClient" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Name
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Client name"
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                            {{ errors.name[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="client@example.com"
                        />
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                            {{ errors.email[0] }}
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button
                            type="button"
                            @click="closeForm"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ saving ? 'Saving...' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Clients List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-gray-500">
                Loading clients...
            </div>

            <div v-else-if="clients.length === 0" class="p-8 text-center text-gray-500">
                No clients found. Create your first client to get started.
            </div>

            <ul v-else class="divide-y divide-gray-200">
                <li
                    v-for="client in clients"
                    :key="client.id"
                    class="p-4 hover:bg-gray-50 flex justify-between items-center"
                >
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ client.name }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ client.email }}
                        </p>
                    </div>
                    <button
                        @click="deleteClient(client.id)"
                        class="text-red-600 hover:text-red-800 text-sm"
                    >
                        Delete
                    </button>
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
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const clients = ref([]);
const loading = ref(false);
const error = ref(null);
const showForm = ref(false);
const saving = ref(false);
const errors = ref({});

const form = reactive({
    name: '',
    email: ''
});

const fetchClients = async () => {
    loading.value = true;
    error.value = null;
    
    try {
        const response = await axios.get('/api/clients');
        clients.value = response.data.data;
    } catch (err) {
        error.value = 'Failed to load clients. Please try again.';
        console.error('Error fetching clients:', err);
    } finally {
        loading.value = false;
    }
};

const closeForm = () => {
    showForm.value = false;
    form.name = '';
    form.email = '';
    errors.value = {};
};

const saveClient = async () => {
    saving.value = true;
    errors.value = {};
    
    try {
        await axios.post('/api/clients', {
            name: form.name,
            email: form.email
        });
        closeForm();
        fetchClients();
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors || {};
        } else {
            error.value = 'Failed to create client. Please try again.';
        }
    } finally {
        saving.value = false;
    }
};

const deleteClient = async (id) => {
    if (!confirm('Are you sure you want to delete this client? This will also delete all their bookings.')) {
        return;
    }
    
    try {
        await axios.delete(`/api/clients/${id}`);
        fetchClients();
    } catch (err) {
        error.value = 'Failed to delete client. Please try again.';
        console.error('Error deleting client:', err);
    }
};

onMounted(() => {
    fetchClients();
});
</script>
