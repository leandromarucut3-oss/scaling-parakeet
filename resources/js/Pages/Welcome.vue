<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-[#f4f6f4] relative overflow-hidden">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(16,185,129,0.10),transparent_40%),radial-gradient(circle_at_80%_10%,rgba(34,197,94,0.10),transparent_35%),radial-gradient(circle_at_50%_80%,rgba(2,132,199,0.06),transparent_45%)]"
        ></div>

        <div v-if="canLogin" class="absolute top-0 right-0 p-6 text-end">
            <Link
                v-if="$page.props.auth.user"
                :href="route('dashboard')"
                class="font-semibold text-emerald-900 hover:text-emerald-950"
            >
                Dashboard
            </Link>

            <template v-else>
                <Link :href="route('login')" class="font-semibold text-emerald-900 hover:text-emerald-950">
                    Log in
                </Link>

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="ms-4 font-semibold text-emerald-900 hover:text-emerald-950"
                >
                    Register
                </Link>
            </template>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-16">
            <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                <div>
                    <div class="flex items-center gap-3">
                        <ApplicationLogo class="h-12 w-auto" />
                    </div>

                    <h1 class="mt-6 text-4xl font-semibold text-emerald-950 lg:text-5xl">
                        Investor Portal
                    </h1>
                    <p class="mt-4 text-lg text-slate-600">
                        Access shareholder information, reports, and market updates in one secure space.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="inline-flex items-center justify-center rounded-md bg-emerald-800 px-6 py-3 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                        >
                            Go to dashboard
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="inline-flex items-center justify-center rounded-md bg-emerald-800 px-6 py-3 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                        >
                            Sign in
                        </Link>
                        <Link
                            v-if="!$page.props.auth.user && canRegister"
                            :href="route('register')"
                            class="inline-flex items-center justify-center rounded-md border border-emerald-200 bg-white px-6 py-3 text-sm font-semibold uppercase tracking-widest text-emerald-900 transition hover:bg-emerald-50"
                        >
                            Register interest
                        </Link>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/95 p-8 shadow-xl ring-1 ring-emerald-100">
                    <div class="text-sm font-semibold text-emerald-800">At a glance</div>
                    <div class="mt-2 text-2xl font-semibold text-emerald-950">Latest investor highlights</div>
                    <div class="mt-6 space-y-4 text-sm text-slate-600">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-2 w-2 rounded-full bg-emerald-600"></div>
                            <div>Annual report and accounts are now available.</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-2 w-2 rounded-full bg-emerald-600"></div>
                            <div>Upcoming results webcast schedule posted.</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-2 w-2 rounded-full bg-emerald-600"></div>
                            <div>Dividend statements ready for download.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16 grid gap-6 md:grid-cols-3">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="text-sm font-semibold text-emerald-800">Reports</div>
                    <p class="mt-3 text-sm text-slate-600">
                        Access financial statements, KPIs, and performance summaries.
                    </p>
                </div>
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="text-sm font-semibold text-emerald-800">Shareholder services</div>
                    <p class="mt-3 text-sm text-slate-600">
                        Manage preferences, dividends, and communications in one place.
                    </p>
                </div>
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="text-sm font-semibold text-emerald-800">Support</div>
                    <p class="mt-3 text-sm text-slate-600">
                        Need help? Contact the investor relations team for assistance.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
