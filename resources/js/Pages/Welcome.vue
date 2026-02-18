<script setup>
import { Head, Link } from '@inertiajs/vue3';

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
    { label: 'Bookings tracked', value: '18K+' },
    { label: 'Happy teams', value: '240' },
    { label: 'Conflicts prevented', value: '4.6M' },
];

const features = [
    'Conflict-free scheduling with instant validation',
    'Client timelines, notes, and history in one view',
    'Week and list views optimized for hybrid teams',
    'API-first architecture with Laravel Sanctum security',
];

const steps = [
    {
        title: 'Invite colleagues & clients',
        body: 'Create authenticated accounts in seconds. Every action is logged, encrypted, and ready for audit.',
    },
    {
        title: 'Plan the perfect week',
        body: 'Drag, drop, and share curated schedules that surface conflicts before they happen.',
    },
    {
        title: 'Ship with confidence',
        body: 'Pair powerful reporting with our Booking API to synchronize calendars, CRMs, and billing tools.',
    },
];
</script>

<template>
    <Head title="Booking Platform" />

    <div class="relative overflow-hidden bg-slate-950 text-slate-100">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -right-32 top-16 h-72 w-72 rounded-full bg-teal-400 blur-[160px] opacity-30"></div>
            <div class="absolute -left-10 bottom-0 h-96 w-96 rounded-full bg-indigo-500 blur-[200px] opacity-30"></div>
        </div>

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 pb-16 pt-10 lg:px-0">
            <header class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-xl font-bold">
                        BP
                    </div>
                    <div>
                        <p class="text-sm uppercase tracking-[0.25em] text-slate-400">Booking Platform</p>
                        <p class="text-base text-slate-200">Modern scheduling for ambitious teams</p>
                    </div>
                </div>

                <nav v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-full border border-white/20 px-5 py-2 text-sm font-medium text-white transition hover:border-white hover:bg-white/10"
                    >
                        Enter dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-full border border-white/20 px-4 py-2 text-sm font-medium text-white transition hover:border-white hover:bg-white/10"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-full bg-gradient-to-r from-teal-400 to-indigo-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:opacity-95"
                        >
                            Create account
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="mt-16 grid gap-16 lg:grid-cols-2 lg:items-center lg:gap-12">
                <div class="space-y-10">
                    <div class="space-y-6">
                        <p class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-teal-200">
                            Booking intelligence
                        </p>
                        <h1 class="text-4xl font-semibold leading-tight text-white sm:text-5xl">
                            Craft polished schedules, eliminate conflicts, and share client-ready itineraries in minutes.
                        </h1>
                        <p class="text-lg text-slate-300">
                            Booking Platform stitches bookings, clients, and availability into one elegant command center. Built on Laravel 12, Vue 3, and Tailwind, it keeps your team aligned without spreadsheet chaos.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <Link
                            :href="canLogin ? ($page.props.auth.user ? route('dashboard') : route('login')) : '#'"
                            class="flex-1 min-w-[180px] rounded-2xl bg-gradient-to-r from-teal-400 to-indigo-500 px-6 py-4 text-center text-base font-semibold text-white shadow-xl shadow-indigo-500/25 transition hover:scale-[1.01]"
                        >
                            Launch the workspace
                        </Link>
                        <Link
                            :href="route('bookings.index')"
                            class="flex-1 min-w-[180px] rounded-2xl border border-white/15 px-6 py-4 text-center text-base font-semibold text-white/80 transition hover:border-white/40 hover:text-white"
                        >
                            Explore the UI
                        </Link>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <div v-for="item in stats" :key="item.label" class="rounded-3xl border border-white/10 bg-white/5 px-6 py-5 text-center">
                            <p class="text-3xl font-semibold text-white">{{ item.value }}</p>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">{{ item.label }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/40">
                        <p class="text-sm font-semibold uppercase tracking-[0.4em] text-teal-200">What you get</p>
                        <ul class="mt-6 space-y-4 text-base text-slate-200">
                            <li v-for="feature in features" :key="feature" class="flex gap-3">
                                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-teal-500/30 text-sm text-teal-100">&#10003;</span>
                                <span>{{ feature }}</span>
                            </li>
                        </ul>

                        <div class="mt-8 rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 p-6">
                            <p class="text-sm uppercase tracking-[0.4em] text-indigo-200/80">How it works</p>
                            <div class="mt-5 space-y-5">
                                <div
                                    v-for="(step, index) in steps"
                                    :key="step.title"
                                    class="rounded-2xl border border-white/10 bg-white/5 p-4"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500/30 text-sm font-semibold text-white">
                                            {{ index + 1 }}
                                        </div>
                                        <p class="font-semibold">{{ step.title }}</p>
                                    </div>
                                    <p class="mt-2 text-sm text-slate-300">{{ step.body }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <section class="mt-24 grid gap-8 rounded-3xl border border-white/10 bg-white/5 p-8 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    <p class="text-sm uppercase tracking-[0.4em] text-teal-200">Why teams switch</p>
                    <h2 class="text-2xl font-semibold text-white">“We replaced three spreadsheets, a CRM, and countless DMs. Booking Platform catches overlaps instantly and keeps our clients impressed.”</h2>
                    <p class="text-sm text-slate-300">Nia Patel · Operations Lead, Latitude Studios</p>
                </div>
                <div class="rounded-2xl bg-slate-900/60 p-6">
                    <p class="text-sm uppercase tracking-[0.4em] text-indigo-200">Integrations</p>
                    <p class="mt-4 text-lg text-slate-200">Connect calendars, CRMs, and billing apps through the Booking Platform API in minutes.</p>
                    <div class="mt-6 flex flex-wrap gap-3 text-xs font-semibold text-slate-300">
                        <span class="rounded-full border border-white/10 px-4 py-1">Google Workspace</span>
                        <span class="rounded-full border border-white/10 px-4 py-1">Slack</span>
                        <span class="rounded-full border border-white/10 px-4 py-1">HubSpot</span>
                        <span class="rounded-full border border-white/10 px-4 py-1">QuickBooks</span>
                    </div>
                </div>
            </section>

            <footer class="mt-20 text-sm text-slate-400">
                Built with Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }} · Tailwind v4
            </footer>
        </div>
    </div>
</template>
