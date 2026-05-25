<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    recentTransactions: {
        type: Array,
        default: () => [],
    },
});

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const formatDateTime = (value) => {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
    }).format(new Date(value));
};
</script>

<template>
    <Head title="Recent Transactions" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="mb-6">
                        <h1 class="text-xl font-semibold text-emerald-900">Recent Transactions</h1>
                        <p class="mt-2 text-sm text-slate-500">View the latest transactions across the platform.</p>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-emerald-100">
                        <div class="max-h-[720px] overflow-y-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-emerald-50/80 text-emerald-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Date</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em]">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="transaction in props.recentTransactions"
                                        :key="`${transaction.type}-${transaction.id}`"
                                        class="border-t border-emerald-100/60 hover:bg-emerald-50/70"
                                    >
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-emerald-950">{{ transaction.user?.name || '—' }}</div>
                                            <div class="text-xs text-slate-500">{{ transaction.user?.email || '' }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-emerald-800 uppercase tracking-[0.12em]">{{ transaction.type }}</td>
                                        <td class="px-4 py-4">
                                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ transaction.status }}</div>
                                            <div v-if="transaction.payment_source" class="mt-1 text-xs font-medium normal-case tracking-normal text-slate-500">
                                                {{ transaction.payment_source }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-xs text-slate-500">{{ formatDateTime(transaction.created_at) }}</td>
                                        <td class="px-4 py-4 text-right font-semibold text-emerald-950">{{ formatCurrency(transaction.amount_cents) }}</td>
                                    </tr>

                                    <tr v-if="!props.recentTransactions.length">
                                        <td class="px-4 py-6 text-center text-sm text-slate-500" colspan="5">
                                            No recent transactions yet.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
