<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    withdrawals: {
        type: Array,
        default: () => [],
    },
});

const withdrawalAction = useForm({});
const copiedId = ref(null);

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const submitWithdrawalAction = (id, action) => {
    const routeName = action === 'approve'
        ? 'admin.withdrawals.approve'
        : 'admin.withdrawals.reject';

    withdrawalAction.post(route(routeName, id), {
        preserveScroll: true,
    });
};

const copyAccount = async (id, accountNumber) => {
    if (!accountNumber) {
        return;
    }

    try {
        await navigator.clipboard.writeText(accountNumber);
        copiedId.value = id;
        setTimeout(() => {
            if (copiedId.value === id) {
                copiedId.value = null;
            }
        }, 2000);
    } catch (error) {
        copiedId.value = null;
    }
};
</script>

<template>
    <Head title="Withdrawals" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="text-sm font-semibold text-emerald-800">Withdrawal requests</div>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-emerald-100">
                        <div class="max-h-[520px] overflow-y-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-emerald-50/80 text-emerald-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Amount</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Bank</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Account</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Status</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em]">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="withdrawal in props.withdrawals"
                                        :key="withdrawal.id"
                                        class="border-t border-emerald-100/60"
                                    >
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-emerald-950">{{ withdrawal.user?.name }}</div>
                                            <div class="text-xs text-slate-500">{{ withdrawal.user?.email }}</div>
                                        </td>
                                        <td class="px-4 py-4 font-semibold text-emerald-950">
                                            {{ formatCurrency(withdrawal.amount_cents) }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-emerald-950">{{ withdrawal.bank_name }}</div>
                                            <div class="text-xs text-slate-500">{{ withdrawal.bank_account_name }}</div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-semibold text-emerald-950">{{ withdrawal.bank_account_number }}</span>
                                                <button
                                                    type="button"
                                                    class="text-xs font-semibold uppercase tracking-widest text-emerald-700"
                                                    @click="copyAccount(withdrawal.id, withdrawal.bank_account_number)"
                                                >
                                                    {{ copiedId === withdrawal.id ? 'Copied' : 'Copy' }}
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                            {{ withdrawal.status }}
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="rounded-full border border-emerald-200 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-emerald-900"
                                                    :disabled="withdrawalAction.processing || withdrawal.status !== 'pending'"
                                                    @click="submitWithdrawalAction(withdrawal.id, 'approve')"
                                                >
                                                    Approve
                                                </button>
                                                <button
                                                    type="button"
                                                    class="rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-rose-700"
                                                    :disabled="withdrawalAction.processing || withdrawal.status !== 'pending'"
                                                    @click="submitWithdrawalAction(withdrawal.id, 'reject')"
                                                >
                                                    Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!props.withdrawals.length">
                                        <td class="px-4 py-6 text-center text-sm text-slate-500" colspan="6">
                                            No withdrawal requests yet.
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
