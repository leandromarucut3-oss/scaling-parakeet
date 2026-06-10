<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onUnmounted } from 'vue';

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
        durationDays: 120,
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

const page = usePage();
const packageSlots = computed(() => page.props.packages ?? {});

const optionsWithSlots = computed(() =>
    options.map((option) => {
        // Get the specific data for this package key
        const slotData = packageSlots.value[option.key];

        // Ensure we have a number; default to 0 if missing
        const left = slotData?.remaining_slots ?? 0;

        return {
            ...option,
            remainingSlots: left,
            // We keep this in case you need it for progress bars later
            slotCapacity: slotData?.slot_capacity ?? null,
            // This is the formatted string for your UI
            availabilityLabel: `${left} slots left`,
            soldOut: left === 0,
        };
    })
);

const carouselRef = ref(null);
const currentIndex = ref(0);
const isModalOpen = ref(false);
const showReceiptModal = ref(false);
const activePaymentMode = ref(null);
const unavailablePaymentMessage = ref('');
const bankOptions = [
    {
        key: 'bpi',
        name: 'BPI Savings',
        accountName: 'Morrisons',
        logo: '/Bank-of-the-Philippine-Islands-BPI-Fintech-FInance-News-113x88.jpg',
        detailsImage: '/BPI.jpg',
        accountNumber: '4059053788',
        qrData: 'BPI Savings Morrisons 4059053788',
    },
    {
        key: 'security',
        name: 'Security Bank',
        accountName: 'Morrisons',
        logo: '/unnamed.png',
        detailsImage: '/unnamed.png',
        accountNumber: '0000081240668',
        qrData: null,
        noQr: true,
    },
];
const selectedBankKey = ref(bankOptions[0]?.key ?? 'bpi');
const showQrModal = ref(false);
const copiedBankAccountKey = ref(null);

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

let receiptTimer = null;

const openReceiptModal = () => {
    showReceiptModal.value = true;
    if (receiptTimer) {
        clearTimeout(receiptTimer);
    }
    receiptTimer = setTimeout(() => {
        showReceiptModal.value = false;
    }, 3000);
};

watch(receipt, (value) => {
    if (value) {
        openReceiptModal();
    }
});
const formattedMin = computed(() => currency.format(selectedPlan.value.minAmount));
const formattedMax = computed(() => currency.format(selectedPlan.value.maxAmount));

const form = useForm({
    amount: '',
    plan_key: options[0]?.key ?? 'premier',
    payment_method: 'account_balance',
    bank_name: bankOptions[0]?.name ?? 'BPI',
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
    if (!target || !carouselRef.value) {
        return;
    }

    currentIndex.value = index;

    const container = carouselRef.value;
    const left = target.offsetLeft - (container.clientWidth - target.clientWidth) / 2;

    container.scrollTo({
        left: Math.max(0, left),
        behavior: 'smooth',
    });
};

const openModal = () => {
    isModalOpen.value = true;
    form.plan_key = selectedPlan.value.key;
    activePaymentMode.value = null;
    unavailablePaymentMessage.value = '';
};

const closeModal = () => {
    isModalOpen.value = false;
    activePaymentMode.value = null;
    unavailablePaymentMessage.value = '';
};

const closeReceiptModal = () => {
    showReceiptModal.value = false;
};

const submitBalancePurchase = () => {
    form.plan_key = selectedPlan.value.key;
    form.payment_method = 'account_balance';
    form.post(route('shares.purchase'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('amount');
            openReceiptModal();
        },
    });
};

const submitBankTransferPurchase = () => {
    form.plan_key = selectedPlan.value.key;
    form.payment_method = 'bank_transfer';
    form.post(route('shares.purchase'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('amount');
            closeModal();
            openReceiptModal();
        },
    });
};

const togglePaymentMode = (mode) => {
    activePaymentMode.value = activePaymentMode.value === mode ? null : mode;
    unavailablePaymentMessage.value = '';
};

const showUnavailablePayment = (label) => {
    unavailablePaymentMessage.value = `${label} is not available in your region yet.`;
};

const formatMoney = (cents) => currency.format((cents ?? 0) / 100);
const formatRate = (bps) => ((bps ?? 0) / 100).toFixed(2);
const selectedBank = computed(() =>
    bankOptions.find((option) => option.key === selectedBankKey.value) ?? bankOptions[0]
);
const selectBank = (option) => {
    selectedBankKey.value = option.key;
    form.bank_name = option.name;
    openQrModal();
};

const openQrModal = () => {
    showQrModal.value = true;
    copiedBankAccountKey.value = null;
};

const closeQrModal = () => {
    showQrModal.value = false;
};

// Prevent background/body scrolling when any modal is open
const stopBodyScroll = () => {
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
};

const restoreBodyScroll = () => {
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
};

watch([isModalOpen, showQrModal, showReceiptModal], (vals) => {
    const anyOpen = vals.some(Boolean);
    if (anyOpen) {
        stopBodyScroll();
    } else {
        restoreBodyScroll();
    }
});

onUnmounted(() => {
    restoreBodyScroll();
});

const copyAccountNumber = async () => {
    const accountNumber = selectedBank.value?.accountNumber;
    if (!accountNumber) {
        return;
    }

    try {
        await navigator.clipboard.writeText(accountNumber);
        copiedBankAccountKey.value = selectedBank.value.key;
        setTimeout(() => {
            if (copiedBankAccountKey.value === selectedBank.value.key) {
                copiedBankAccountKey.value = null;
            }
        }, 2000);
    } catch (error) {
        copiedBankAccountKey.value = null;
    }
};

const formatPaymentMethod = (method) => {
    if (method === 'account_balance') {
        return 'Account balance';
    }

    if (method === 'bank_transfer') {
        return 'Bank transfer';
    }

    return method ?? 'Unknown';
};

const receiptReferenceNumber = computed(() => {
    if (!receipt.value) {
        return '';
    }

    const rawDate = receipt.value.created_at ? new Date(receipt.value.created_at) : null;
    const dateString = rawDate && !Number.isNaN(rawDate.getTime())
        ? rawDate.toISOString().slice(0, 10).replace(/-/g, '')
        : receipt.value.created_at ?? '';

    return `MRC-FT-${dateString}-${receipt.value.id}`;
});

const receiptTransactionDate = computed(() => {
    if (!receipt.value?.created_at) {
        return '';
    }

    const date = new Date(receipt.value.created_at);
    if (Number.isNaN(date.getTime())) {
        return receipt.value.created_at;
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(date);
});

const receiptDestinationAccount = computed(() => {
    const account = selectedBank.value?.accountNumber ?? '';
    if (account.length <= 4) {
        return account;
    }
    return `**** **** ${account.slice(-4)}`;
});
</script>

<template>
    <Head title="Buy Shares" />

    <AuthenticatedLayout>

        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl bg-emerald-50/70 p-6 shadow-sm border border-emerald-100">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-emerald-900">Morrisons Share Packages</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-emerald-700">
                                Pick a Morrisons package and see how many slots are still available. Each purchase automatically reduces the remaining availability.
                            </p>
                        </div>
                        <div class="rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-emerald-900 shadow-inner border border-emerald-100">
                            Available plans: Premier · Deluxe · Presidential
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div
                        ref="carouselRef"
                        class="relative z-0 flex snap-x snap-mandatory gap-6 overflow-x-scroll pb-4 touch-pan-x overscroll-x-contain [&::-webkit-scrollbar]:hidden"
                        style="scrollbar-width: none; -webkit-overflow-scrolling: touch; touch-action: pan-x;"
                        @scroll.passive="updateIndex"
                    >
                        <div
                            v-for="(option, index) in optionsWithSlots"
                            :key="index"
                            class="min-w-[80%] snap-center sm:min-w-[60%] lg:min-w-[40%]"
                        >
                            <button
                                type="button"
                                class="w-full rounded-3xl bg-white/95 shadow-xl ring-1 ring-emerald-100 overflow-hidden"
                                @click="openModal"
                                aria-label="Open payment options"
                            >
                                <div class="relative aspect-[4961/7016] w-full overflow-hidden rounded-[2rem] bg-white shadow-lg">
                                    <img
                                        :src="option.image"
                                        alt=""
                                        class="h-full w-full object-cover"
                                    />
                                    <div class="absolute inset-x-0 top-0 flex justify-between p-4">
                                        <div
                                            class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white"
                                            :class="option.soldOut ? 'bg-rose-600' : 'bg-emerald-700'"
                                        >
                                            <span v-if="option.soldOut">Sold out</span>
                                            <span v-else>{{ option.remainingSlots }} slots left</span>
                                        </div>
                                        <div class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-700">
                                            {{ option.name }}
                                        </div>
                                    </div>
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/70 to-transparent px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.18em] text-white">
                                        Morrisons investment
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="relative z-30 mt-4 flex items-center justify-center gap-2 pointer-events-auto">
                    <button
                        v-for="(option, index) in options"
                        :key="`dot-${index}`"
                        type="button"
                        class="flex h-6 w-6 items-center justify-center rounded-full"
                        :class="index === currentIndex ? 'bg-emerald-700' : 'bg-emerald-200'"
                        :aria-label="`Go to ${option.name}`"
                        @pointerdown.stop
                        @click.stop.prevent="scrollToIndex(index)"
                    >
                        <span
                            class="h-3 w-3 rounded-full transition"
                            :class="index === currentIndex ? 'bg-emerald-700' : 'bg-emerald-200'"
                        ></span>
                    </button>
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
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl max-h-[90vh] overflow-auto">
                    <div class="flex items-start justify-between">
                        <div class="text-sm font-semibold text-emerald-900">Mode of payment</div>
                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-emerald-100 text-emerald-800 hover:bg-emerald-50"
                            @click="closeModal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div v-if="successMessage" class="mt-3 rounded-lg bg-emerald-50 px-4 py-3 text-xs text-emerald-700">
                        {{ successMessage }}
                    </div>
                    <div class="mt-2 rounded-xl border border-emerald-100 bg-emerald-50/60 p-3 text-xs text-emerald-900">
                        <div class="font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ selectedPlan.name }}</div>
                        <div class="mt-1">Price range: {{ formattedMin }} - {{ formattedMax }}</div>
                        <div>Daily interest: {{ selectedPlan.dailyRate }}% for {{ selectedPlan.durationDays }} days</div>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div
                            v-if="!activePaymentMode || activePaymentMode === 'balance'"
                            class="rounded-xl border border-emerald-100"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                                @click="togglePaymentMode('balance')"
                            >
                                <span>Use balance</span>
                                <span class="text-xs text-emerald-700">
                                    {{ activePaymentMode === 'balance' ? 'Close' : 'Select' }}
                                </span>
                            </button>
                            <div v-if="activePaymentMode === 'balance'" class="border-t border-emerald-100 px-4 py-4">
                                <input
                                    name="amount"
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
                                <button
                                    type="button"
                                    class="mt-4 w-full rounded-lg bg-emerald-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                                    :disabled="form.processing"
                                    @click="submitBalancePurchase"
                                >
                                    Pay with balance
                                </button>
                            </div>
                        </div>
                        <div
                            v-if="!activePaymentMode || activePaymentMode === 'bank_transfer'"
                            class="rounded-xl border border-emerald-100"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                                @click="togglePaymentMode('bank_transfer')"
                            >
                                <span>Bank transfer</span>
                                <span class="text-xs text-emerald-700">
                                    {{ activePaymentMode === 'bank_transfer' ? 'Close' : 'Select' }}
                                </span>
                            </button>
                            <div v-if="activePaymentMode === 'bank_transfer'" class="border-t border-emerald-100 px-4 py-4">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <button
                                        v-for="option in bankOptions"
                                        :key="option.key"
                                        type="button"
                                        class="flex items-center gap-3 rounded-xl border px-3 py-2 text-left text-xs font-semibold uppercase tracking-[0.2em]"
                                        :class="selectedBankKey === option.key
                                            ? 'border-emerald-600 bg-emerald-50 text-emerald-800'
                                            : 'border-emerald-100 text-emerald-700 hover:bg-emerald-50'"
                                        @click="selectBank(option)"
                                        :aria-pressed="selectedBankKey === option.key"
                                    >
                                        <img
                                            :src="option.logo"
                                            :alt="`${option.name} logo`"
                                            class="h-8 w-12 rounded-md border border-emerald-100 bg-white object-contain"
                                        />
                                        <span>{{ option.name }}</span>
                                    </button>
                                </div>
                                <div class="mt-4 text-xs text-emerald-700">
                                    Select a bank option above to view the payment details in a separate modal.
                                </div>
                            </div>
                        </div>
                        <button
                            v-if="!activePaymentMode"
                            type="button"
                            class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                            @click="showUnavailablePayment('Debit or credit card')"
                        >
                            <span>Debit or credit card</span>
                            <span class="text-xs text-emerald-700">Select</span>
                        </button>
                        <button
                            v-if="!activePaymentMode"
                            type="button"
                            class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                            @click="showUnavailablePayment('Mobile wallet')"
                        >
                            <span>Mobile wallet</span>
                            <span class="text-xs text-emerald-700">Select</span>
                        </button>
                        <div
                            v-if="unavailablePaymentMessage && !activePaymentMode"
                            class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800"
                        >
                            {{ unavailablePaymentMessage }}
                        </div>
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

        <div v-if="showQrModal" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-slate-900/50" @click="closeQrModal"></div>
            <div class="relative flex min-h-screen items-center justify-center px-4">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl max-h-[90vh] overflow-auto">
                    <div class="flex items-start justify-between">
                        <div class="text-sm font-semibold text-emerald-900">
                            {{ selectedBank.name }} payment details
                        </div>
                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-emerald-100 text-emerald-800 hover:bg-emerald-50"
                            @click="closeQrModal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900">
                        <div class="font-semibold">
                            <span v-if="selectedBank.key !== 'security'">View the bank transfer details below.</span>
                            <span v-else>Use the Security Bank account details below.</span>
                        </div>
                        <div v-if="selectedBank.key !== 'security'" class="mt-4 flex items-center justify-center">
                            <img
                                :src="selectedBank.detailsImage"
                                :alt="`Bank details for ${selectedBank.name}`"
                                class="rounded-2xl border border-emerald-100 bg-white object-contain max-h-[60vh] max-w-full"
                            />
                        </div>
                        <div v-else class="mt-4 rounded-2xl border border-emerald-100 bg-white p-6 text-center text-sm text-slate-600">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Security Bank account number</div>
                            <button
                                type="button"
                                class="mt-4 w-full rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-left text-sm text-emerald-900 hover:bg-emerald-100"
                                @click="copyAccountNumber"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <span>{{ selectedBank.accountNumber }}</span>
                                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                        {{ copiedBankAccountKey === selectedBank.key ? 'Copied' : 'Tap to copy' }}
                                    </span>
                                </div>
                            </button>
                            <div class="mt-2 text-xs text-slate-500">Tap to copy and complete the transfer from your bank app.</div>
                        </div>
                        <div class="mt-4 rounded-2xl border border-emerald-100 bg-white p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Account holder</div>
                            <div class="mt-1 text-sm font-semibold text-emerald-900">{{ selectedBank.accountName }}</div>
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 mt-3">Account number</div>
                            <div class="mt-1 text-sm text-emerald-900">{{ selectedBank.accountNumber }}</div>
                        </div>
                        <div class="mt-4">
                            <input
                                name="amount"
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
                            <button
                                type="button"
                                class="mt-4 w-full rounded-lg bg-emerald-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                                :disabled="form.processing"
                                @click="submitBankTransferPurchase"
                            >
                                Submit transfer
                            </button>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="mt-5 w-full rounded-full border border-emerald-100 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-900 hover:bg-emerald-50"
                        @click="closeQrModal"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showReceiptModal && receipt" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-slate-900/50" @click="closeReceiptModal"></div>
            <div class="relative flex min-h-screen items-center justify-center px-4">
                <div class="w-full max-w-md rounded-2xl border border-emerald-100 bg-white p-6 text-sm text-emerald-900 shadow-2xl max-h-[90vh] overflow-auto">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Receipt</div>
                        <button
                            type="button"
                            class="text-xs font-semibold uppercase tracking-widest text-emerald-700"
                            @click="closeReceiptModal"
                        >
                            Close
                        </button>
                    </div>
                    <div class="mt-4 space-y-2">
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
    </AuthenticatedLayout>
</template>
