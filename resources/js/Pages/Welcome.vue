<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const stats = [
    { label: 'Teams onboarded', value: '240' },
    { label: 'Bookings tracked', value: '18K' },
    { label: 'Conflicts prevented', value: '4.6M' },
];

const highlights = [
    'Live conflict detection on every booking',
    'Client profiles, notes, and history in one view',
    'Shareable weekly agenda built for hybrid teams',
    'Secure API powered by Laravel Sanctum',
];

const steps = [
    {
        title: 'Connect your team',
        copy: 'Invite coordinators and clients. Everyone gets a clear picture of the week in one secure space.',
    },
    {
        title: 'Plan without clashes',
        copy: 'Create bookings, adjust timing, and resolve overlaps instantly using our repository-driven rules.',
    },
    {
        title: 'Share confidently',
        copy: 'Export clean summaries or give stakeholders read-only access to the calendar they need.',
    },
];
</script>

<template>
    <Head title="Booking Platform" />

    <div class="min-h-screen bg-white text-slate-900">
        <div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-10">
            <header class="flex flex-wrap items-center justify-between border-b border-slate-200 pb-6">
                <div class="flex items-center gap-4">
                    <ApplicationLogo />
                </div>

                <nav v-if="canLogin" class="flex items-center gap-3 text-sm font-medium">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-full border border-[#45b2e9] px-4 py-2 text-[#45b2e9] hover:bg-[#45b2e9]/10"
                    >
                        Go to dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-full border border-slate-200 px-4 py-2 text-slate-700 hover:bg-slate-100"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-full bg-[#45b2e9] px-5 py-2 text-white hover:text-white hover:opacity-50"
                        >
                            Create account
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="grid flex-1 gap-12 py-12 lg:grid-cols-[1.4fr_1fr]">
                <section class="space-y-8">
                    <div class="space-y-4">
                        <h1 class="text-4xl font-semibold leading-tight">
                            Align bookings, clients, and calendars without juggling spreadsheets.
                        </h1>
                        <p class="text-base text-slate-600">
                            Booking Platform combines repository-driven business rules with a clean Vue interface. Plan weeks, prevent overlaps, and share updates with clients in a single, dependable workspace.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <Link
                            :href="canLogin ? ($page.props.auth.user ? route('dashboard') : route('login')) : '#'"
                            class="rounded-full bg-[#45b2e9] px-6 py-3 text-sm font-semibold text-white hover:bg-[#2f85b0]"
                        >
                            Open the workspace
                        </Link>
                        <Link
                            :href="route('bookings.page')"
                            class="rounded-full border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                        >
                            Review features
                        </Link>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div v-for="item in stats" :key="item.label" class="rounded-2xl border border-slate-200 p-4 text-center">
                            <p class="text-3xl font-semibold text-[#45b2e9]">{{ item.value }}</p>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">{{ item.label }}</p>
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 p-6">
                        <p class="text-sm font-semibold text-[#45b2e9]">Highlights</p>
                        <ul class="mt-4 space-y-3 text-sm text-slate-700">
                            <li v-for="feature in highlights" :key="feature" class="flex gap-3">
                                <span class="mt-1 inline-flex h-4 w-4 items-center justify-center rounded-full border border-[#45b2e9] text-[10px] text-[#45b2e9]">&#10003;</span>
                                <span>{{ feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="space-y-4 rounded-2xl border border-slate-200 p-6">
                        <p class="text-sm font-semibold text-[#45b2e9]">Workflow</p>
                        <div class="space-y-4">
                            <div v-for="(step, index) in steps" :key="step.title" class="rounded-xl border border-slate-100 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.4em] text-slate-400">Step {{ index + 1 }}</p>
                                <h3 class="mt-1 text-base font-semibold text-slate-900">{{ step.title }}</h3>
                                <p class="text-sm text-slate-600">{{ step.copy }}</p>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <section class="space-y-6 rounded-2xl border border-slate-200 p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="max-w-3xl space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.4em] text-[#45b2e9]">Customer note</p>
                        <p class="text-lg font-semibold">“We run a distributed production team. Booking Platform replaced four tools, catches conflicts immediately, and sends clean summaries to clients every Friday.”</p>
                        <p class="text-sm text-slate-600">Nia Patel · Operations Lead, Latitude Studios</p>
                    </div>
                    <div class="flex gap-3 text-sm font-medium text-slate-600">
                        <span class="rounded-full border border-slate-200 px-3 py-1">Google Workspace</span>
                        <span class="rounded-full border border-slate-200 px-3 py-1">Slack</span>
                        <span class="rounded-full border border-slate-200 px-3 py-1">HubSpot</span>
                    </div>
                </div>
            </section>

            <footer class="mt-12 border-t border-slate-200 pt-6 text-sm text-slate-500">
                &copy; {{ new Date().getFullYear() }} Booking Platform. All rights reserved.
            </footer>
        </div>
    </div>
</template>
