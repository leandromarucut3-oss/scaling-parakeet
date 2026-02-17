<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const totalInvestmentCents = computed(() => page.props.metrics?.total_investment_cents ?? 0);
const interestEarnedCents = computed(() => page.props.metrics?.interest_earned_cents ?? 0);
const dailyInterestCents = computed(() => page.props.metrics?.daily_interest_cents ?? 0);
const recentActivity = computed(() => page.props.recent_activity ?? []);
const showBankNotice = ref(false);
const showWithdrawModal = ref(false);
const withdrawalSuccess = computed(() => page.props.flash?.withdrawal_success ?? '');

const hasBankDetails = computed(() => {
    const details = user.value;
    return Boolean(
        details?.bank_name &&
        details?.bank_account_name &&
        details?.bank_account_number
    );
});

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const formattedBalance = computed(() =>
    currency.format(((user.value?.balance_cents ?? 0) / 100))
);

const formattedTotalInvestment = computed(() =>
    currency.format((totalInvestmentCents.value ?? 0) / 100)
);

const formattedInterestEarned = computed(() =>
    currency.format((interestEarnedCents.value ?? 0) / 100)
);

const formattedDailyInterest = computed(() =>
    currency.format((dailyInterestCents.value ?? 0) / 100)
);

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const withdrawForm = useForm({
    amount: '',
});

const handleWithdrawClick = () => {
    if (!hasBankDetails.value) {
        showBankNotice.value = true;
        return;
    }

    showWithdrawModal.value = true;
};

const submitWithdrawal = () => {
    withdrawForm.post(route('withdrawals.store'), {
        preserveScroll: true,
        onSuccess: () => {
            withdrawForm.reset('amount');
            showWithdrawModal.value = false;
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>

        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-700 p-8 text-white shadow-xl">
                    <div class="absolute -right-10 -top-14 h-40 w-40 rounded-full bg-white/10"></div>
                    <div class="absolute bottom-0 right-16 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-100">
                            Morrisons investor wallet
                        </div>
                        <div class="mt-4 text-3xl font-semibold sm:text-4xl">Account balance</div>
                        <div class="mt-3 text-4xl font-semibold sm:text-5xl">{{ formattedBalance }}</div>
                        <div v-if="user" class="mt-4 text-sm text-emerald-100">
                            Welcome back, <span class="font-semibold text-white">{{ user.name }}</span>
                            <span class="text-emerald-200">({{ user.email }})</span>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <Link
                                :href="route('shares.buy')"
                                class="inline-flex items-center rounded-full bg-white px-5 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-900 transition hover:bg-emerald-50"
                            >
                                Buy shares
                            </Link>
                            <button
                                class="inline-flex items-center rounded-full border border-white/40 px-5 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-white/10"
                                type="button"
                                @click="handleWithdrawClick"
                            >
                                Withdraw
                            </button>
                            <button
                                class="inline-flex items-center rounded-full border border-white/40 px-5 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-white/10"
                                type="button"
                            >
                                Statements
                            </button>
                        </div>
                        <div v-if="showBankNotice && !hasBankDetails" class="mt-4 rounded-lg bg-white/10 px-4 py-3 text-xs text-white">
                            Add your bank details before requesting a withdrawal.
                            <Link :href="route('profile.edit')" class="font-semibold underline">Update profile</Link>.
                        </div>
                        <div v-if="withdrawalSuccess" class="mt-4 rounded-lg bg-white/10 px-4 py-3 text-xs text-white">
                            {{ withdrawalSuccess }}
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                            Total investment
                        </div>
                        <div class="mt-3 text-3xl font-semibold text-emerald-950">{{ formattedTotalInvestment }}</div>
                        <div class="mt-2 text-xs text-slate-500">Sum of all completed purchases.</div>
                    </div>

                    <div class="rounded-2xl bg-emerald-800 p-6 text-white shadow-xl">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100">
                            Morrisons <span class="text-amber-300">Daily</span> earnings
                        </div>
                        <div class="mt-3 text-3xl font-semibold sm:text-4xl">{{ formattedDailyInterest }}</div>
                        <div class="mt-2 text-xs text-emerald-100">
                            Total Interest Earned: <span class="font-semibold text-white">{{ formattedInterestEarned }}</span>
                        </div>
                    </div>

                </div>

                <div>
                    <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold text-emerald-900">Recent activity</div>
                            <button class="text-xs font-semibold text-emerald-800" type="button">View all</button>
                        </div>
                        <div class="mt-4 space-y-4 text-sm text-slate-600">
                            <div
                                v-for="item in recentActivity"
                                :key="item.id"
                                class="flex items-center justify-between rounded-xl bg-emerald-50/60 px-4 py-3"
                            >
                                <div>
                                    <div class="font-semibold text-emerald-900">{{ item.title }}</div>
                                    <div class="text-xs text-slate-500">{{ item.status }}</div>
                                </div>
                                <div class="font-semibold text-emerald-900">
                                    {{ formatCurrency(item.amount_cents) }}
                                </div>
                            </div>
                            <div v-if="!recentActivity.length" class="rounded-xl bg-emerald-50/60 px-4 py-3 text-sm text-slate-500">
                                No activity yet.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showWithdrawModal" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-slate-900/50" @click="showWithdrawModal = false"></div>
            <div class="relative flex min-h-screen items-center justify-center px-4">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="text-sm font-semibold text-emerald-900">Request withdrawal</div>
                    <div class="mt-4">
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600" for="withdraw_amount">Amount (USD)</label>
                        <input
                            id="withdraw_amount"
                            v-model="withdrawForm.amount"
                            type="number"
                            step="0.01"
                            min="20"
                            class="mt-2 w-full rounded-lg border border-emerald-100 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Enter amount"
                        />
                        <div v-if="withdrawForm.errors.amount" class="mt-2 text-xs text-rose-600">
                            {{ withdrawForm.errors.amount }}
                        </div>
                    </div>
                    <div class="mt-5 flex gap-3">
                        <button
                            type="button"
                            class="flex-1 rounded-lg bg-emerald-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                            :disabled="withdrawForm.processing"
                            @click="submitWithdrawal"
                        >
                            Submit
                        </button>
                        <button
                            type="button"
                            class="flex-1 rounded-lg border border-emerald-100 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-900"
                            @click="showWithdrawModal = false"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
