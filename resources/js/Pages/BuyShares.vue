<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const options = [
    {
        key: 'premier',
        name: 'Premier',
        minAmount: 150,
        maxAmount: 799,
        dailyRate: 0.5,
        durationDays: 150,
        image: '/SPINNEYS%20MARKETING%20TOOLS%20(17).jpg',
    },
    {
        key: 'deluxe',
        name: 'Deluxe',
        minAmount: 800,
        maxAmount: 7999,
        dailyRate: 0.7,
        durationDays: 150,
        image: '/SPINNEYS%20MARKETING%20TOOLS%20(18).jpg',
    },
    {
        key: 'presidential',
        name: 'Presidential',
        minAmount: 8000,
        maxAmount: 1000000,
        dailyRate: 0.9,
        durationDays: 90,
        image: '/SPINNEYS%20MARKETING%20TOOLS%20(19).jpg',
    },
];

const carouselRef = ref(null);
const currentIndex = ref(0);
const isModalOpen = ref(false);
const bankTransferImage = '/unnamed%20(3).jpg';

const page = usePage();
const balanceCents = computed(() => page.props.auth?.user?.balance_cents ?? 0);
const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});
const formattedBalance = computed(() => currency.format(balanceCents.value / 100));
const receipt = computed(() => page.props.flash?.purchase_receipt ?? null);
const successMessage = computed(() => page.props.flash?.success ?? '');
const selectedPlan = computed(() => options[currentIndex.value] ?? options[0]);
const formattedMin = computed(() => currency.format(selectedPlan.value.minAmount));
const formattedMax = computed(() => currency.format(selectedPlan.value.maxAmount));

const form = useForm({
    amount: '',
    plan_key: options[0]?.key ?? 'premier',
    payment_method: 'account_balance',
});

const getSlides = () => {
    if (!carouselRef.value) {
        return [];
    }

    return Array.from(carouselRef.value.children);
};

const updateIndex = () => {
    const slides = getSlides();
    if (slides.length === 0) {
        return;
    }

    const containerRect = carouselRef.value.getBoundingClientRect();
    const containerCenter = containerRect.left + containerRect.width / 2;
    let closestIndex = 0;
    let closestDistance = Number.POSITIVE_INFINITY;

    slides.forEach((slide, index) => {
        const slideRect = slide.getBoundingClientRect();
        const slideCenter = slideRect.left + slideRect.width / 2;
        const distance = Math.abs(slideCenter - containerCenter);

        if (distance < closestDistance) {
            closestDistance = distance;
            closestIndex = index;
        }
    });

    currentIndex.value = closestIndex;
};

const scrollToIndex = (index) => {
    const slides = getSlides();
    const target = slides[index];
    if (!target) {
        return;
    }

    target.scrollIntoView({
        behavior: 'smooth',
        inline: 'center',
        block: 'nearest',
    });
};

const openModal = () => {
    isModalOpen.value = true;
    form.plan_key = selectedPlan.value.key;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submitBalancePurchase = () => {
    form.plan_key = selectedPlan.value.key;
    form.payment_method = 'account_balance';
    form.post(route('shares.purchase'), {
        preserveScroll: true,
        onSuccess: () => form.reset('amount'),
    });
};

const submitBankTransferPurchase = () => {
    form.plan_key = selectedPlan.value.key;
    form.payment_method = 'bank_transfer';
    form.post(route('shares.purchase'), {
        preserveScroll: true,
        onSuccess: () => form.reset('amount'),
    });
};

const formatMoney = (cents) => currency.format((cents ?? 0) / 100);
const formatRate = (bps) => ((bps ?? 0) / 100).toFixed(2);
const formatPaymentMethod = (method) => {
    if (method === 'account_balance') {
        return 'Account balance';
    }

    if (method === 'bank_transfer') {
        return 'Bank transfer';
    }

    return method ?? 'Unknown';
};
</script>

<template>
    <Head title="Buy Shares" />

    <AuthenticatedLayout>

        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="relative">
                    <div
                        ref="carouselRef"
                        class="flex snap-x snap-mandatory gap-6 overflow-x-scroll pb-4 touch-pan-x overscroll-x-contain [&::-webkit-scrollbar]:hidden"
                        style="scrollbar-width: none; -webkit-overflow-scrolling: touch; touch-action: pan-x;"
                        @scroll.passive="updateIndex"
                    >
                        <div
                            v-for="(option, index) in options"
                            :key="index"
                            class="min-w-[80%] snap-center sm:min-w-[60%] lg:min-w-[40%]"
                        >
                            <button
                                type="button"
                                class="w-full rounded-3xl bg-white/95 shadow-xl ring-1 ring-emerald-100 overflow-hidden"
                                @click="openModal"
                                aria-label="Open payment options"
                            >
                                <div class="aspect-[4961/7016] w-full bg-white">
                                    <img
                                        :src="option.image"
                                        alt=""
                                        class="h-full w-full object-contain"
                                    />
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <span
                        v-for="(option, index) in options"
                        :key="`dot-${index}`"
                        class="h-2 w-2 rounded-full transition"
                        :class="index === currentIndex ? 'bg-emerald-700' : 'bg-emerald-200'"
                        role="button"
                        tabindex="0"
                        @click="scrollToIndex(index)"
                        @keydown.enter.prevent="scrollToIndex(index)"
                        @keydown.space.prevent="scrollToIndex(index)"
                    ></span>
                </div>
                <div
                    v-if="receipt"
                    class="rounded-2xl border border-emerald-100 bg-white/95 p-6 text-sm text-emerald-900 shadow-lg"
                >
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Receipt</div>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Reference</span>
                            <span class="font-semibold">#{{ receipt.id }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Plan</span>
                            <span class="font-semibold">{{ receipt.plan_name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Amount</span>
                            <span class="font-semibold">{{ formatMoney(receipt.amount_cents) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Daily interest</span>
                            <span class="font-semibold">{{ formatRate(receipt.daily_interest_bps) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Duration</span>
                            <span class="font-semibold">{{ receipt.duration_days }} days</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Payment</span>
                            <span class="font-semibold">{{ formatPaymentMethod(receipt.payment_method) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Status</span>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide"
                                :class="receipt.status === 'pending'
                                    ? 'bg-orange-100 text-orange-700'
                                    : 'bg-emerald-100 text-emerald-700'"
                            >
                                {{ receipt.status }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Date</span>
                            <span class="font-semibold">{{ receipt.created_at }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-slate-900/50" @click="closeModal"></div>
            <div class="relative flex min-h-screen items-center justify-center px-4">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="text-sm font-semibold text-emerald-900">Mode of payment</div>
                    <div v-if="successMessage" class="mt-3 rounded-lg bg-emerald-50 px-4 py-3 text-xs text-emerald-700">
                        {{ successMessage }}
                    </div>
                    <div class="mt-2 rounded-xl border border-emerald-100 bg-emerald-50/60 p-3 text-xs text-emerald-900">
                        <div class="font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ selectedPlan.name }}</div>
                        <div class="mt-1">Price range: {{ formattedMin }} - {{ formattedMax }}</div>
                        <div>Daily interest: {{ selectedPlan.dailyRate }}% for {{ selectedPlan.durationDays }} days</div>
                    </div>
                    <div class="mt-4 space-y-3">
                        <details class="group rounded-xl border border-emerald-100">
                            <summary
                                class="flex w-full cursor-pointer list-none items-center justify-between rounded-xl px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                            >
                                <span>Use balance</span>
                                <span class="text-xs text-emerald-700 group-open:rotate-90">Select</span>
                            </summary>
                            <div class="border-t border-emerald-100 px-4 py-4">
                                <input
                                    v-model="form.amount"
                                    type="number"
                                    step="0.01"
                                    :min="selectedPlan.minAmount"
                                    :max="selectedPlan.maxAmount"
                                    class="w-full rounded-lg border border-emerald-100 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Enter amount"
                                />
                                <div v-if="form.errors.amount" class="mt-2 text-xs text-rose-600">
                                    {{ form.errors.amount }}
                                </div>
                            </div>
                            <button
                                type="button"
                                class="mx-4 mb-4 w-[calc(100%-2rem)] rounded-lg bg-emerald-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                                :disabled="form.processing"
                                @click="submitBalancePurchase"
                            >
                                Pay with balance
                            </button>
                        </details>
                        <details class="group rounded-xl border border-emerald-100">
                            <summary
                                class="flex w-full cursor-pointer list-none items-center justify-between rounded-xl px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                            >
                                <span>Bank transfer</span>
                                <span class="text-xs text-emerald-700 group-open:rotate-90">Select</span>
                            </summary>
                            <div class="border-t border-emerald-100 px-4 py-4">
                                <img
                                    :src="bankTransferImage"
                                    alt="Bank transfer details"
                                    class="mb-4 w-full rounded-lg border border-emerald-100"
                                />
                                <div class="text-xs text-emerald-700">
                                    Use the details above to complete your transfer, then enter the amount you paid.
                                </div>
                                <input
                                    v-model="form.amount"
                                    type="number"
                                    step="0.01"
                                    :min="selectedPlan.minAmount"
                                    :max="selectedPlan.maxAmount"
                                    class="mt-3 w-full rounded-lg border border-emerald-100 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Enter amount"
                                />
                                <div v-if="form.errors.amount" class="mt-2 text-xs text-rose-600">
                                    {{ form.errors.amount }}
                                </div>
                            </div>
                            <button
                                type="button"
                                class="mx-4 mb-4 w-[calc(100%-2rem)] rounded-lg bg-emerald-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                                :disabled="form.processing"
                                @click="submitBankTransferPurchase"
                            >
                                Submit transfer
                            </button>
                        </details>
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                        >
                            <span>Debit or credit card</span>
                            <span class="text-xs text-emerald-700">Select</span>
                        </button>
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                        >
                            <span>Mobile wallet</span>
                            <span class="text-xs text-emerald-700">Select</span>
                        </button>
                    </div>
                    <button
                        type="button"
                        class="mt-5 w-full rounded-full border border-emerald-100 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-900"
                        @click="closeModal"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
