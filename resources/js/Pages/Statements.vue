<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    deposits: {
        type: Array,
        default: () => [],
    },
    withdrawals: {
        type: Array,
        default: () => [],
    },
    interest: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('deposits');

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const tabLabels = {
    deposits: 'Deposits',
    withdrawals: 'Withdrawals',
    interest: 'Interest',
};

const activeItems = computed(() => {
    if (activeTab.value === 'withdrawals') {
        return props.withdrawals;
    }

    if (activeTab.value === 'interest') {
        return props.interest;
    }

    return props.deposits;
});
</script>

<template>
    <Head title="Statements" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-emerald-800">Statements</div>
                            <div class="text-xs text-slate-500">
                                Review your deposits, withdrawals, and interest history.
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="(label, key) in tabLabels"
                                :key="key"
                                type="button"
                                class="rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition"
                                :class="activeTab === key
                                    ? 'border-emerald-700 bg-emerald-700 text-white'
                                    : 'border-emerald-100 text-emerald-800 hover:bg-emerald-50'"
                                @click="activeTab = key"
                            >
                                {{ label }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-2xl border border-emerald-100">
                        <div class="max-h-[520px] overflow-y-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-emerald-50/80 text-emerald-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Details</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Status</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em]">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in activeItems"
                                        :key="`${activeTab}-${item.id}`"
                                        class="border-t border-emerald-100/60"
                                    >
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-emerald-950">
                                                <span v-if="activeTab === 'deposits'">
                                                    {{ item.plan_name || 'Deposit' }}
                                                </span>
                                                <span v-else-if="activeTab === 'withdrawals'">
                                                    Withdrawal request
                                                </span>
                                                <span v-else>
                                                    Interest earned - {{ item.plan_name || 'Plan' }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ item.created_at || '—' }}
                                            </div>
                                            <div v-if="activeTab === 'deposits'" class="text-xs text-slate-500">
                                                {{ item.payment_method || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                            {{ item.status || 'credited' }}
                                        </td>
                                        <td class="px-4 py-4 text-right font-semibold text-emerald-950">
                                            {{ formatCurrency(item.amount_cents) }}
                                        </td>
                                    </tr>
                                    <tr v-if="!activeItems.length">
                                        <td class="px-4 py-6 text-center text-sm text-slate-500" colspan="3">
                                            No statements for {{ tabLabels[activeTab] }} yet.
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
