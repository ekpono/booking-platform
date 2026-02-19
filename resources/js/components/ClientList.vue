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

            <template v-else>
                <ul class="divide-y divide-gray-200">
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

                <!-- Pagination -->
                <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
                    <div class="text-sm text-slate-700">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                    </div>
                    <div class="flex gap-1">
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-1 rounded-md border border-slate-300 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            @click="goToPage(page)"
                            :class="[
                                'px-3 py-1 rounded-md border text-sm font-medium',
                                page === pagination.current_page
                                    ? 'bg-[#45b2e9] text-white border-[#45b2e9]'
                                    : 'border-slate-300 text-slate-700 hover:bg-slate-50'
                            ]"
                        >
                            {{ page }}
                        </button>
                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-1 rounded-md border border-slate-300 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import { ensureCsrfCookie } from '@/utils/csrf';

const clients = ref([]);
const loading = ref(false);
const error = ref(null);
const showForm = ref(false);
const saving = ref(false);
const errors = ref({});
const currentPage = ref(1);
const perPage = ref(12);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
    per_page: 12
});

const form = reactive({
    name: '',
    email: ''
});

const visiblePages = computed(() => {
    const pages = [];
    const start = Math.max(1, pagination.value.current_page - 2);
    const end = Math.min(pagination.value.last_page, pagination.value.current_page + 2);
    
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
});

const fetchClients = async (page = 1) => {
    loading.value = true;
    error.value = null;
    currentPage.value = page;
    
    try {
        await ensureCsrfCookie();
        const response = await axios.get(`/api/clients?page=${page}&per_page=${perPage.value}`);
        clients.value = response.data.data;
        
        // Update pagination info
        if (response.data.meta) {
            pagination.value = {
                current_page: response.data.meta.current_page,
                last_page: response.data.meta.last_page,
                from: response.data.meta.from,
                to: response.data.meta.to,
                total: response.data.meta.total,
                per_page: response.data.meta.per_page
            };
            perPage.value = response.data.meta.per_page;
        }
    } catch (err) {
        error.value = 'Failed to load clients. Please try again.';
        console.error('Error fetching clients:', err);
    } finally {
        loading.value = false;
    }
};

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchClients(page);
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
        fetchClients(currentPage.value);
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
        fetchClients(currentPage.value);
    } catch (err) {
        error.value = 'Failed to delete client. Please try again.';
        console.error('Error deleting client:', err);
    }
};

onMounted(() => {
    fetchClients(1);
});
</script>
