<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    appUrl: {
        type: String,
        default: '',
    },
});

const search = ref('');

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
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

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const referralUrl = (referralCode) => {
    if (!referralCode) {
        return '';
    }

    return `${props.appUrl.replace(/\/$/, '')}/register/${encodeURIComponent(referralCode)}`;
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

const formatTimeAgo = (value) => {
    if (!value) {
        return '—';
    }

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    const now = new Date();
    const seconds = Math.floor((now - parsed) / 1000);

    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    const weeks = Math.floor(days / 7);
    if (weeks < 4) return `${weeks}w ago`;
    const months = Math.floor(days / 30);
    if (months < 12) return `${months}mo ago`;
    const years = Math.floor(months / 12);
    return `${years}y ago`;
};

const showUserModal = ref(false);
const selectedUser = ref(null);
const loadingUser = ref(false);
const userError = ref('');

const openUserModal = async (user) => {
    showUserModal.value = true;
    selectedUser.value = null;
    userError.value = '';
    loadingUser.value = true;

    try {
        const response = await window.axios.get(route('admin.users.show', { user: user.id }));
        selectedUser.value = response.data;
    } catch (error) {
        userError.value = 'Unable to load user details. Please refresh and try again.';
    } finally {
        loadingUser.value = false;
    }
};

const closeUserModal = () => {
    showUserModal.value = false;
    selectedUser.value = null;
    userError.value = '';
    loadingUser.value = false;
};

const deleteDeposit = async (depositId) => {
    if (!selectedUser.value) {
        return;
    }

    if (!window.confirm('Delete this deposit? This will remove the record and stop it from accruing further interest.')) {
        return;
    }

    try {
        await window.axios.delete(route('admin.users.deposit.destroy', {
            user: selectedUser.value.id,
            purchase: depositId,
        }));

        selectedUser.value.deposit_history = selectedUser.value.deposit_history.filter(
            (deposit) => deposit.id !== depositId,
        );
    } catch (error) {
        userError.value = 'Unable to delete this deposit. Please try again.';
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-emerald-800">Users</div>
                            <div class="text-xs text-slate-500">{{ filteredUsers.length }} accounts</div>
                        </div>
                        <div class="w-full max-w-sm">
                            <TextInput
                                v-model="search"
                                class="w-full"
                                type="text"
                                placeholder="Search name, email, ID"
                            />
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-2xl border border-emerald-100">
                        <div class="max-h-[720px] overflow-y-auto">
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
                                        class="border-t border-emerald-100/60 hover:bg-emerald-50/70 cursor-pointer"
                                        @click="openUserModal(user)"
                                    >
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-emerald-950">{{ user.name }}</div>
                                        <div v-if="user.referral_code" class="mt-1 text-xs text-emerald-700 break-words">
                                            <a
                                                :href="referralUrl(user.referral_code)"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="hover:text-emerald-900"
                                            >
                                                {{ referralUrl(user.referral_code) }}
                                            </a>
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">{{ user.email }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-xs text-emerald-800">
                                            <span v-if="user.roles?.length" class="rounded-full bg-emerald-100 px-3 py-1">
                                                {{ user.roles.join(', ') }}
                                            </span>
                                            <span v-else class="text-slate-400">User</span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-emerald-950">
                                            {{ user.referrer?.name || '—' }}
                                            <div v-if="user.referrer?.email" class="text-xs text-slate-500">
                                                {{ user.referrer.email }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-xs text-slate-500">
                                            <div class="font-semibold text-emerald-700">{{ formatTimeAgo(user.created_at) }}</div>
                                            <div class="text-slate-400">{{ formatDate(user.created_at) }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-right font-semibold text-emerald-950">{{ formatCurrency(user.balance_cents) }}</td>
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
            </div>
        </div>

        <div v-if="showUserModal" class="fixed inset-0 z-50 overflow-y-auto px-4 py-8 sm:px-6 lg:px-8">
            <div class="absolute inset-0 bg-slate-900/70" @click="closeUserModal"></div>
            <div class="relative mx-auto w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/10">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">User account details</h2>
                        <p class="mt-1 text-sm text-slate-500">Review account info, bank details, and transaction history.</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        @click="closeUserModal"
                    >
                        Close
                    </button>
                </div>

                <div class="p-6">
                    <template v-if="loadingUser">
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center text-slate-500">
                            Loading user details...
                        </div>
                    </template>

                    <template v-else-if="userError">
                        <div class="rounded-3xl border border-rose-100 bg-rose-50 p-6 text-sm text-rose-700">
                            {{ userError }}
                        </div>
                    </template>

                    <template v-else-if="selectedUser">
                        <div class="grid gap-4 lg:grid-cols-3">
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Account</div>
                                <div class="mt-3 text-base font-semibold text-slate-900">{{ selectedUser.name }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ selectedUser.email }}</div>
                                <div class="mt-3 text-xs uppercase tracking-[0.2em] text-slate-500">Joined</div>
                                <div class="mt-1 text-sm text-slate-900">{{ formatDate(selectedUser.created_at) }}</div>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Balance</div>
                                <div class="mt-3 text-base font-semibold text-emerald-900">{{ formatCurrency(selectedUser.balance_cents) }}</div>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Referrer</div>
                                <div class="mt-3 text-sm text-slate-900">{{ selectedUser.referrer?.name || '—' }}</div>
                                <div v-if="selectedUser.referrer?.email" class="mt-1 text-xs text-slate-500">{{ selectedUser.referrer.email }}</div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Bank details</div>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div>
                                    <div class="text-xs text-slate-500">Bank</div>
                                    <div class="mt-1 text-sm text-slate-900">{{ selectedUser.bank_name || '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500">Account name</div>
                                    <div class="mt-1 text-sm text-slate-900">{{ selectedUser.bank_account_name || '—' }}</div>
                                </div>
                                <div class="sm:col-span-2">
                                    <div class="text-xs text-slate-500">Account number</div>
                                    <div class="mt-1 text-sm text-slate-900 break-all">{{ selectedUser.bank_account_number || '—' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="mb-4 flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-900">Deposit history</h3>
                                        <p class="text-xs text-slate-500">Recent deposits made by this user.</p>
                                    </div>
                                </div>
                                <div class="overflow-hidden rounded-3xl border border-slate-200">
                                    <table class="w-full border-collapse text-sm">
                                        <thead class="bg-slate-50 text-slate-800">
                                            <tr>
                                                <th class="px-3 py-3 text-left uppercase tracking-[0.18em] text-[0.65rem]">Date</th>
                                                <th class="px-3 py-3 text-left uppercase tracking-[0.18em] text-[0.65rem]">Amount</th>
                                                <th class="px-3 py-3 text-left uppercase tracking-[0.18em] text-[0.65rem]">Status</th>
                                                <th class="px-3 py-3 text-left uppercase tracking-[0.18em] text-[0.65rem]">Package</th>
                                                <th class="px-3 py-3 text-right uppercase tracking-[0.18em] text-[0.65rem]">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="deposit in selectedUser.deposit_history" :key="`deposit-${deposit.id}`" class="border-t border-slate-200">
                                                <td class="px-3 py-3 text-slate-700">
                                                    <div>{{ formatTimeAgo(deposit.created_at) }}</div>
                                                    <div class="text-xs text-slate-500">{{ formatDate(deposit.created_at) }}</div>
                                                </td>
                                                <td class="px-3 py-3 font-semibold text-emerald-900">{{ formatCurrency(deposit.amount_cents) }}</td>
                                                <td class="px-3 py-3 uppercase text-xs tracking-[0.18em] text-slate-600">{{ deposit.status }}</td>
                                                <td class="px-3 py-3 text-slate-700">{{ deposit.plan_name || 'Package purchase' }}</td>
                                                <td class="px-3 py-3 text-right">
                                                    <button
                                                        type="button"
                                                        class="rounded-full border border-rose-200 bg-white px-3 py-1 text-xs font-semibold uppercase tracking-widest text-rose-700 hover:bg-rose-50"
                                                        @click.stop="deleteDeposit(deposit.id)"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="!selectedUser.deposit_history.length">
                                                <td class="px-3 py-6 text-center text-sm text-slate-500" colspan="5">No deposit history available.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="mb-4 flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-900">Withdrawal history</h3>
                                        <p class="text-xs text-slate-500">Recent withdrawal requests by this user.</p>
                                    </div>
                                </div>
                                <div class="overflow-hidden rounded-3xl border border-slate-200">
                                    <table class="w-full border-collapse text-sm">
                                        <thead class="bg-slate-50 text-slate-800">
                                            <tr>
                                                <th class="px-3 py-3 text-left uppercase tracking-[0.18em] text-[0.65rem]">Date</th>
                                                <th class="px-3 py-3 text-left uppercase tracking-[0.18em] text-[0.65rem]">Amount</th>
                                                <th class="px-3 py-3 text-left uppercase tracking-[0.18em] text-[0.65rem]">Status</th>
                                                <th class="px-3 py-3 text-left uppercase tracking-[0.18em] text-[0.65rem]">Bank</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="withdrawal in selectedUser.withdrawal_history" :key="`withdrawal-${withdrawal.id}`" class="border-t border-slate-200">
                                                <td class="px-3 py-3 text-slate-700">
                                                    <div>{{ formatTimeAgo(withdrawal.created_at) }}</div>
                                                    <div class="text-xs text-slate-500">{{ formatDate(withdrawal.created_at) }}</div>
                                                </td>
                                                <td class="px-3 py-3 font-semibold text-emerald-900">{{ formatCurrency(withdrawal.amount_cents) }}</td>
                                                <td class="px-3 py-3 uppercase text-xs tracking-[0.18em] text-slate-600">{{ withdrawal.status }}</td>
                                                <td class="px-3 py-3 text-slate-700">{{ withdrawal.bank_name || '—' }}</td>
                                            </tr>
                                            <tr v-if="!selectedUser.withdrawal_history.length">
                                                <td class="px-3 py-6 text-center text-sm text-slate-500" colspan="4">No withdrawal history available.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
