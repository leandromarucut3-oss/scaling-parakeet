<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    deposits: {
        type: Array,
        default: () => [],
    },
});

const depositAction = useForm({});
const copiedId = ref(null);

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const submitDepositApproval = (id) => {
    depositAction.post(route('admin.deposits.approve', id), {
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
    <Head title="Deposits" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="text-sm font-semibold text-emerald-800">Deposits</div>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-emerald-100">
                        <div class="max-h-[520px] overflow-y-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-emerald-50/80 text-emerald-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Plan</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Amount</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Bank</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Status</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em]">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="deposit in props.deposits"
                                        :key="deposit.id"
                                        class="border-t border-emerald-100/60"
                                    >
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-emerald-950">{{ deposit.user?.name }}</div>
                                            <div class="text-xs text-slate-500">{{ deposit.user?.email }}</div>
                                        </td>
                                        <td class="px-4 py-4 font-semibold text-emerald-950">
                                            {{ deposit.plan_name }}
                                        </td>
                                        <td class="px-4 py-4 font-semibold text-emerald-950">
                                            {{ formatCurrency(deposit.amount_cents) }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-emerald-950">{{ deposit.user?.bank_name }}</div>
                                            <div class="text-xs text-slate-500">{{ deposit.user?.bank_account_name }}</div>
                                            <div class="mt-1 flex items-center justify-between gap-2">
                                                <span class="font-semibold text-emerald-950">{{ deposit.user?.bank_account_number }}</span>
                                                <button
                                                    type="button"
                                                    class="text-xs font-semibold uppercase tracking-widest text-emerald-700"
                                                    @click="copyAccount(deposit.id, deposit.user?.bank_account_number)"
                                                >
                                                    {{ copiedId === deposit.id ? 'Copied' : 'Copy' }}
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                            {{ deposit.status }}
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <button
                                                type="button"
                                                class="rounded-full border border-emerald-200 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-emerald-900"
                                                :disabled="depositAction.processing || deposit.status !== 'pending'"
                                                @click="submitDepositApproval(deposit.id)"
                                            >
                                                Approve
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!props.deposits.length">
                                        <td class="px-4 py-6 text-center text-sm text-slate-500" colspan="6">
                                            No deposits yet.
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
