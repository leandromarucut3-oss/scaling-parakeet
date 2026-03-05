<script setup>
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    adminBalanceCents: {
        type: Number,
        default: 0,
    },
    withdrawals: {
        type: Array,
        default: () => [],
    },
    recentTransactions: {
        type: Array,
        default: () => [],
    },
});

const packageOptions = [
    {
        key: 'premier',
        name: 'Premier',
        minAmount: 150,
        maxAmount: 799,
        dailyRate: 0.5,
        durationDays: 150,
    },
    {
        key: 'deluxe',
        name: 'Deluxe',
        minAmount: 800,
        maxAmount: 7999,
        dailyRate: 0.7,
        durationDays: 120,
    },
    {
        key: 'presidential',
        name: 'Presidential',
        minAmount: 8000,
        maxAmount: 1000000,
        dailyRate: 0.9,
        durationDays: 90,
    },
];

const search = ref('');
const selectedUserId = ref(props.users[0]?.id ?? null);
const form = useForm({
    amount: '',
});

const packageForm = useForm({
    plan_key: packageOptions[0]?.key ?? 'premier',
    amount: packageOptions[0]?.minAmount ?? 150,
});

const withdrawalAction = useForm({});

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const dateTimeFormatter = new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

const dateFormatter = new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
});

const filteredUsers = computed(() => {
    const base = search.value
        ? props.users.filter((user) => {
            const needle = search.value.toLowerCase();

            return (
                user.name?.toLowerCase().includes(needle) ||
                user.email?.toLowerCase().includes(needle) ||
                String(user.id).includes(needle)
            );
        })
        : props.users;

    return [...base].sort((a, b) => {
        const aDate = a.created_at ? new Date(a.created_at).getTime() : 0;
        const bDate = b.created_at ? new Date(b.created_at).getTime() : 0;

        return bDate - aDate;
    });
});

const selectedUser = computed(() =>
    props.users.find((user) => user.id === selectedUserId.value)
);

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const formatDateTime = (value) => {
    if (!value) {
        return '—';
    }

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    return dateTimeFormatter.format(parsed);
};

const formatDate = (value) => {
    if (!value) {
        return '—';
    }

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    return dateFormatter.format(parsed);
};

const selectedPlan = computed(() =>
    packageOptions.find((option) => option.key === packageForm.plan_key) ?? packageOptions[0]
);

watch(
    () => packageForm.plan_key,
    () => {
        if (selectedPlan.value) {
            packageForm.amount = selectedPlan.value.minAmount;
        }
    }
);

const submitTransfer = () => {
    if (!selectedUser.value) {
        return;
    }

    form.post(route('admin.users.transfer', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => form.reset('amount'),
    });
};

const submitPackageGrant = () => {
    if (!selectedUser.value) {
        return;
    }

    packageForm.post(route('admin.users.grant-package', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => packageForm.reset('amount'),
    });
};

const submitWithdrawalAction = (id, action) => {
    const routeName = action === 'approve'
        ? 'admin.withdrawals.approve'
        : 'admin.withdrawals.reject';

    withdrawalAction.post(route(routeName, id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Admin" />

    <AuthenticatedLayout>

        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <div class="space-y-6">
                        <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-emerald-800">Users</div>
                                    <div class="text-xs text-slate-500">{{ filteredUsers.length }} accounts</div>
                                </div>
                                <div class="w-full sm:w-64">
                                    <TextInput
                                        v-model="search"
                                        class="w-full"
                                        type="text"
                                        placeholder="Search name, email, ID"
                                    />
                                </div>
                            </div>

                            <div class="mt-4 overflow-hidden rounded-2xl border border-emerald-100">
                                <div class="max-h-[420px] overflow-y-auto">
                                    <table class="w-full border-collapse text-sm">
                                        <thead class="bg-emerald-50/80 text-emerald-900">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">User</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Role</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Referrer</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Joined</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em]">Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="user in filteredUsers"
                                                :key="user.id"
                                                class="cursor-pointer border-t border-emerald-100/60 transition hover:bg-emerald-50/70"
                                                :class="{
                                                    'bg-emerald-100/60': user.id === selectedUserId,
                                                }"
                                                @click="selectedUserId = user.id"
                                            >
                                                <td class="px-4 py-4">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <div class="font-semibold text-emerald-950">{{ user.name }}</div>
                                                        <span
                                                            v-if="user.is_new"
                                                            class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-700"
                                                        >
                                                            New
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-slate-500">{{ user.email }}</div>
                                                </td>
                                                <td class="px-4 py-4 text-xs text-emerald-800">
                                                    <span v-if="user.roles?.length" class="rounded-full bg-emerald-100 px-3 py-1">
                                                        {{ user.roles.join(', ') }}
                                                    </span>
                                                    <span v-else class="text-slate-400">User</span>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="text-sm font-semibold text-emerald-950">
                                                        {{ user.referrer?.name || '—' }}
                                                    </div>
                                                    <div v-if="user.referrer?.email" class="text-xs text-slate-500">
                                                        {{ user.referrer.email }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 text-xs text-slate-500">
                                                    {{ formatDate(user.created_at) }}
                                                </td>
                                                <td class="px-4 py-4 text-right font-semibold text-emerald-950">
                                                    {{ formatCurrency(user.balance_cents) }}
                                                </td>
                                            </tr>
                                            <tr v-if="!filteredUsers.length">
                                                <td class="px-4 py-6 text-center text-sm text-slate-500" colspan="5">
                                                    No users match this search.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-emerald-800">Recent transactions</div>
                                    <div class="text-xs text-slate-500">Latest activity across the system</div>
                                </div>
                            </div>
                            <div class="mt-4 overflow-hidden rounded-2xl border border-emerald-100">
                                <div class="max-h-[360px] overflow-y-auto">
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
                                                class="border-t border-emerald-100/60"
                                            >
                                                <td class="px-4 py-4">
                                                    <div class="font-semibold text-emerald-950">
                                                        {{ transaction.user?.name || '—' }}
                                                    </div>
                                                    <div class="text-xs text-slate-500">{{ transaction.user?.email || '' }}</div>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                                            {{ transaction.type === 'purchase' ? 'Package' : 'Withdrawal' }}
                                                        </div>
                                                        <span
                                                            v-if="transaction.type === 'purchase' && transaction.is_new"
                                                            class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-700"
                                                        >
                                                            New deposit
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-slate-500">{{ transaction.description }}</div>
                                                </td>
                                                <td class="px-4 py-4 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                                    {{ transaction.status }}
                                                </td>
                                                <td class="px-4 py-4 text-xs text-slate-500">
                                                    {{ formatDateTime(transaction.created_at) }}
                                                </td>
                                                <td class="px-4 py-4 text-right font-semibold text-emerald-950">
                                                    {{ formatCurrency(transaction.amount_cents) }}
                                                </td>
                                            </tr>
                                            <tr v-if="!props.recentTransactions.length">
                                                <td class="px-4 py-6 text-center text-sm text-slate-500" colspan="4">
                                                    No recent transactions yet.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                            <div class="text-sm font-semibold text-emerald-800">Selected user</div>

                            <div v-if="selectedUser" class="mt-4 space-y-4 text-sm">
                                <div>
                                    <div class="text-xs uppercase tracking-[0.2em] text-emerald-500">Name</div>
                                    <div class="text-lg font-semibold text-emerald-950">{{ selectedUser.name }}</div>
                                </div>
                                <div>
                                    <div class="text-xs uppercase tracking-[0.2em] text-emerald-500">Email</div>
                                    <div class="text-sm font-semibold text-emerald-900">{{ selectedUser.email }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-xs uppercase tracking-[0.2em] text-emerald-500">User ID</div>
                                        <div class="text-sm font-semibold text-emerald-900">#{{ selectedUser.id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs uppercase tracking-[0.2em] text-emerald-500">Joined</div>
                                        <div class="text-sm font-semibold text-emerald-900">
                                            {{ selectedUser.created_at || '—' }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs uppercase tracking-[0.2em] text-emerald-500">Balance</div>
                                    <div class="text-2xl font-semibold text-emerald-950">
                                        {{ formatCurrency(selectedUser.balance_cents) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs uppercase tracking-[0.2em] text-emerald-500">Roles</div>
                                    <div class="text-sm font-semibold text-emerald-900">
                                        {{ selectedUser.roles?.length ? selectedUser.roles.join(', ') : 'User' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs uppercase tracking-[0.2em] text-emerald-500">Referrer</div>
                                    <div class="text-sm font-semibold text-emerald-900">
                                        {{ selectedUser.referrer?.name || '—' }}
                                    </div>
                                    <div v-if="selectedUser.referrer?.email" class="text-xs text-slate-500">
                                        {{ selectedUser.referrer.email }}
                                    </div>
                                </div>
                            </div>
                            <div v-else class="mt-4 text-sm text-slate-500">Select a user to view details.</div>
                        </div>

                        <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                            <div class="text-sm font-semibold text-emerald-800">Send funds</div>
                            <form class="mt-4 space-y-4" @submit.prevent="submitTransfer">
                                <div>
                                    <InputLabel for="amount" value="Amount (USD)" />
                                    <TextInput
                                        id="amount"
                                        v-model="form.amount"
                                        class="mt-1 block w-full"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        :disabled="!selectedUser"
                                    />
                                    <InputError class="mt-2" :message="form.errors.amount" />
                                </div>
                                <PrimaryButton
                                    type="submit"
                                    :disabled="form.processing || !selectedUser"
                                    class="w-full justify-center"
                                >
                                    Send to {{ selectedUser ? selectedUser.name : 'user' }}
                                </PrimaryButton>
                                <div v-if="form.recentlySuccessful" class="text-xs text-emerald-600">
                                    Funds sent successfully.
                                </div>
                            </form>
                        </div>

                        <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                            <div class="text-sm font-semibold text-emerald-800">Send package</div>
                            <form class="mt-4 space-y-4" @submit.prevent="submitPackageGrant">
                                <div>
                                    <InputLabel for="plan_key" value="Package" />
                                    <select
                                        id="plan_key"
                                        v-model="packageForm.plan_key"
                                        class="mt-1 block w-full rounded-md border border-emerald-100 bg-white px-3 py-2 text-sm text-emerald-900 focus:border-emerald-500 focus:ring-emerald-500"
                                        :disabled="!selectedUser"
                                    >
                                        <option
                                            v-for="option in packageOptions"
                                            :key="option.key"
                                            :value="option.key"
                                        >
                                            {{ option.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="packageForm.errors.plan_key" />
                                </div>
                                <div class="rounded-lg border border-emerald-100 bg-emerald-50/60 px-3 py-2 text-xs text-emerald-800">
                                    <div class="font-semibold">{{ selectedPlan.name }} details</div>
                                    <div>Range: {{ formatCurrency(selectedPlan.minAmount * 100) }} - {{ formatCurrency(selectedPlan.maxAmount * 100) }}</div>
                                    <div>Daily interest: {{ selectedPlan.dailyRate }}% for {{ selectedPlan.durationDays }} days</div>
                                </div>
                                <div>
                                    <InputLabel for="package_amount" value="Amount (USD)" />
                                    <TextInput
                                        id="package_amount"
                                        v-model="packageForm.amount"
                                        class="mt-1 block w-full"
                                        type="number"
                                        step="0.01"
                                        :min="selectedPlan.minAmount"
                                        :max="selectedPlan.maxAmount"
                                        :disabled="!selectedUser"
                                    />
                                    <InputError class="mt-2" :message="packageForm.errors.amount" />
                                </div>
                                <PrimaryButton
                                    type="submit"
                                    :disabled="packageForm.processing || !selectedUser"
                                    class="w-full justify-center"
                                >
                                    Send {{ selectedPlan.name }} to {{ selectedUser ? selectedUser.name : 'user' }}
                                </PrimaryButton>
                                <div v-if="packageForm.recentlySuccessful" class="text-xs text-emerald-600">
                                    Package sent successfully.
                                </div>
                            </form>
                        </div>

                        <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                            <div class="text-sm font-semibold text-emerald-800">Withdrawal requests</div>
                            <div class="mt-4 overflow-hidden rounded-2xl border border-emerald-100">
                                <div class="max-h-[320px] overflow-y-auto">
                                    <table class="w-full border-collapse text-sm">
                                        <thead class="bg-emerald-50/80 text-emerald-900">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">User</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Amount</th>
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
                                                    <div class="mt-2 text-xs text-slate-500">
                                                        {{ withdrawal.bank_name }} · {{ withdrawal.bank_account_name }} · {{ withdrawal.bank_account_number }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4 font-semibold text-emerald-950">
                                                    {{ formatCurrency(withdrawal.amount_cents) }}
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
                                                <td class="px-4 py-6 text-center text-sm text-slate-500" colspan="4">
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>
