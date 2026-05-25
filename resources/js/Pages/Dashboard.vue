<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const totalInvestmentCents = computed(() => Number(page.props.metrics?.total_investment_cents ?? 0));
const interestEarnedCents = computed(() => Number(page.props.metrics?.interest_earned_cents ?? 0));
const dailyInterestCents = computed(() => Number(page.props.metrics?.daily_interest_cents ?? 0));
const recentActivity = computed(() => page.props.recent_activity ?? []);
const showBankNotice = ref(false);
const showWithdrawModal = ref(false);
const withdrawalSuccess = computed(() => page.props.flash?.withdrawal_success ?? '');
const accountSummaryImage = 'https://my.morrisons.com/globalassets/hubs/more-reasons/category-and-location---location.png';
const dashboardBackground = 'https://www.morrisons.com/images/home/header-leaf-right.svg';
const dashboardBackgroundLeft = 'https://www.morrisons.com/images/home/header-leaf-left.svg';

const dashboardBgStyle = computed(() => ({
    backgroundImage: `url(${dashboardBackground}), url(${dashboardBackgroundLeft})`,
    backgroundRepeat: 'no-repeat, no-repeat',
    backgroundPosition: 'right top, left bottom',
    backgroundSize: '40%, 70%'
}));

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
    currency.format((Number(user.value?.balance_cents ?? 0) / 100))
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

const portfolioValueCents = computed(() =>
    Number(user.value?.balance_cents ?? 0) + (totalInvestmentCents.value ?? 0)
);

const formattedPortfolioValue = computed(() =>
    currency.format((portfolioValueCents.value ?? 0) / 100)
);

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const formatTimeAgo = (value) => {
    if (!value) {
        return '—';
    }

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    const timeFormatter = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });

    return timeFormatter.format(parsed);
};

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

        <div class="py-10 relative" :style="dashboardBgStyle">
            <div class="max-w-7xl mx-auto space-y-8 px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-2xl bg-emerald-950 p-6 text-white shadow-lg">
                    <img
                        :src="accountSummaryImage"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover object-center"
                    />
                    <div class="absolute inset-0 bg-emerald-950/70"></div>
                    <div class="absolute -right-10 -top-14 h-40 w-40 rounded-full bg-white/5"></div>
                    <div class="absolute bottom-2 right-16 h-24 w-24 rounded-full bg-white/5"></div>
                    <div class="relative">
                        
                        <div class="mt-3 text-lg font-semibold text-emerald-100">Available balance</div>
                        <div class="mt-2 text-[clamp(2.2rem,10vw,3rem)] font-bold leading-none sm:text-5xl">{{ formattedBalance }}</div>
                        

                        <div class="mt-5 flex flex-row flex-wrap items-center gap-2">
                            <Link
                                :href="route('shares.buy')"
                                class="inline-flex items-center rounded-md bg-white px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-emerald-900 transition hover:bg-emerald-50"
                            >
                                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSrLhJJYrRl2-kWkb2COMUm474kWhz1NExBFzJeJOa14A&s" alt="invest" class="h-4 w-4 mr-2 object-contain" />
                                Invest
                            </Link>
                            <button
                                class="inline-flex items-center rounded-md border border-white/30 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-white transition hover:bg-white/10"
                                type="button"
                                @click="handleWithdrawClick"
                            >
                                Withdraw
                            </button>
                            <Link
                                :href="route('transfer.index')"
                                class="inline-flex items-center rounded-md border border-white/30 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-white transition hover:bg-white/10"
                            >
                                Transfer
                            </Link>
                            <Link
                                :href="route('statements')"
                                class="inline-flex items-center rounded-md border border-white/30 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-white transition hover:bg-white/10"
                            >
                                Statements
                            </Link>
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

                <div class="grid grid-cols-3 gap-3 sm:gap-5">
                    <div class="flex min-h-[136px] min-w-0 flex-col rounded-xl bg-white/95 p-3 shadow-md ring-1 ring-emerald-100 sm:min-h-0 sm:rounded-2xl sm:p-5">
                        <div class="min-h-[2rem] break-words text-[0.58rem] font-semibold uppercase leading-4 tracking-[0.08em] text-emerald-700 sm:min-h-0 sm:text-[11px] sm:tracking-[0.15em]">
                            Total invested capital
                        </div>
                        <div class="mt-2 whitespace-nowrap text-[clamp(1rem,4.3vw,1.45rem)] font-bold leading-tight text-emerald-950 sm:text-2xl">{{ formattedTotalInvestment }}</div>
                        <div class="mt-auto pt-2 text-[0.68rem] leading-4 text-slate-500 sm:mt-2 sm:pt-0 sm:text-xs">Sum of all completed purchases.</div>
                    </div>

                    <div class="flex min-h-[136px] min-w-0 flex-col rounded-xl bg-white/95 p-3 shadow-md ring-1 ring-emerald-100 sm:min-h-0 sm:rounded-2xl sm:p-5">
                        <div class="min-h-[2rem] break-words text-[0.58rem] font-semibold uppercase leading-4 tracking-[0.08em] text-emerald-700 sm:min-h-0 sm:text-[11px] sm:tracking-[0.15em]">
                            Portfolio value
                        </div>
                        <div class="mt-2 whitespace-nowrap text-[clamp(1rem,4.3vw,1.45rem)] font-bold leading-tight text-emerald-950 sm:text-2xl">{{ formattedPortfolioValue }}</div>
                        <div class="mt-auto pt-2 text-[0.68rem] leading-4 text-slate-500 sm:mt-2 sm:pt-0 sm:text-xs">Balance plus invested capital.</div>
                    </div>

                    <div class="relative flex min-h-[136px] min-w-0 flex-col overflow-hidden rounded-xl bg-emerald-950 p-3 text-white shadow-lg sm:min-h-0 sm:rounded-2xl sm:p-5">
                        <img
                            :src="accountSummaryImage"
                            alt=""
                            class="absolute inset-0 h-full w-full object-cover object-center"
                        />
                        <div class="absolute inset-0 bg-emerald-950/70"></div>
                        <div class="relative flex h-full flex-col">
                            <div class="min-h-[2rem] break-words text-[0.58rem] font-semibold uppercase leading-4 tracking-[0.08em] text-emerald-100 sm:min-h-0 sm:text-[11px] sm:tracking-[0.15em]">
                                Daily interest
                            </div>
                            <div class="mt-2 whitespace-nowrap text-[clamp(1rem,4.3vw,1.45rem)] font-bold leading-tight sm:text-2xl">{{ formattedDailyInterest }}</div>
                            <div class="mt-auto pt-2 text-[0.68rem] leading-4 text-emerald-100 sm:mt-2 sm:pt-0 sm:text-xs">
                                Total interest earned: <span class="font-semibold text-white">{{ formattedInterestEarned }}</span>
                            </div>
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
                                    <div class="text-xs text-slate-500">{{ formatTimeAgo(item.created_at) }} • {{ item.status }}</div>
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
