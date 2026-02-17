<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    deposits: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Deposits" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="text-sm font-semibold text-emerald-800">Deposits</div>
                    <div class="mt-4 text-sm text-slate-500">
                        No deposits available yet.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
