<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen relative overflow-hidden bg-[#f4f6f4]">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(16,185,129,0.08),transparent_40%),radial-gradient(circle_at_80%_10%,rgba(34,197,94,0.08),transparent_35%),radial-gradient(circle_at_50%_80%,rgba(2,132,199,0.05),transparent_45%)]"
        ></div>

        <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <Link href="/" class="mb-6 flex items-center gap-3">
                <ApplicationLogo class="h-10 w-auto" />
            </Link>

            <div class="w-full max-w-xl rounded-2xl bg-white/95 p-10 shadow-xl ring-1 ring-slate-900/5">
                <slot />
            </div>
        </div>
    </div>
</template>
