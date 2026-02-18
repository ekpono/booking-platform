<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">Clients</h2>
            <button
                @click="showForm = true"
                class="rounded-full bg-[#45b2e9] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#3794c0]"
            >
                New Client
            </button>
        </div>

        <!-- Client Form Modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">
                        New Client
                    </h3>
                </div>

                <form @submit.prevent="saveClient" class="space-y-4 p-6">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            Name
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-[#45b2e9] focus:ring-[#45b2e9]"
                            placeholder="Client name"
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                            {{ errors.name[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-[#45b2e9] focus:ring-[#45b2e9]"
                            placeholder="client@example.com"
                        />
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                            {{ errors.email[0] }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeForm"
                            class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="rounded-full bg-[#45b2e9] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#3794c0] disabled:opacity-40"
                        >
                            {{ saving ? 'Saving...' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Clients List -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div v-if="loading" class="p-8 text-center text-slate-500">
                Loading clients...
            </div>

            <div v-else-if="clients.length === 0" class="p-8 text-center text-slate-500">
                No clients found. Create your first client to get started.
            </div>

            <ul v-else class="divide-y divide-gray-200">
                <li
                    v-for="client in clients"
                    :key="client.id"
                    class="flex items-center justify-between p-4 hover:bg-slate-50"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ client.name }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            {{ client.email }}
                        </p>
                    </div>
                    <button
                        @click="deleteClient(client.id)"
                        class="text-sm font-medium text-slate-500 hover:text-slate-900"
                    >
                        Delete
                    </button>
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
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { ensureCsrfCookie } from '@/utils/csrf';

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
        await ensureCsrfCookie();
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
        await ensureCsrfCookie();
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
        await ensureCsrfCookie();
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
