<template>
    <AuthenticatedLayout title="Transfer Funds">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Transfer Funds
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <form @submit.prevent="submitTransfer" class="space-y-6">
                            <div>
                                <InputLabel for="recipient_email" value="Recipient Email" />
                                <TextInput
                                    id="recipient_email"
                                    v-model="form.recipient_email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.recipient_email" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="amount" value="Amount" />
                                <TextInput
                                    id="amount"
                                    v-model="form.amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.amount" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end">
                                <PrimaryButton :disabled="form.processing">
                                    Transfer Funds
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showReceiptModal && receipt" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="absolute inset-0 bg-slate-900/50" @click="closeReceiptModal"></div>
            <div class="relative mx-auto my-8 w-full max-w-3xl rounded-[22px] bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="relative bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 px-6 py-8 sm:px-10 sm:py-10 text-white">
                    <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5"></div>
                    <div class="relative z-10">
                        <div class="text-2xl font-semibold tracking-tight">MORRISONS</div>
                        <div class="mt-2 text-sm text-slate-200">Commercial & General Merchandise Co.</div>
                        <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white backdrop-blur">
                            <span>✓ Transfer Successfully Completed</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="absolute top-4 right-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white hover:bg-white/20"
                        @click="closeReceiptModal"
                        aria-label="Close receipt"
                    >
                        ×
                    </button>
                </div>

                <div class="p-6 sm:p-10">
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Fund Transfer Receipt</div>

                    <div class="mt-6 rounded-[18px] bg-slate-50 border border-slate-200 p-6">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Transfer Amount</div>
                        <div class="mt-3 text-4xl font-semibold text-slate-900">{{ formattedAmount }}</div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[18px] bg-slate-50 border border-slate-200 p-5">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sender</div>
                            <div class="mt-3 text-sm font-semibold text-slate-900">{{ receipt.sender_business }}</div>
                            <div class="mt-2 text-sm text-slate-600">{{ receipt.sender_name }}</div>
                        </div>
                        <div class="rounded-[18px] bg-slate-50 border border-slate-200 p-5">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Recipient</div>
                            <div class="mt-3 text-sm font-semibold text-slate-900">{{ receipt.recipient_name }}</div>
                            <div class="mt-2 text-sm text-slate-600">{{ receipt.recipient_method }}</div>
                        </div>
                    </div>

                    <div class="mt-8 rounded-[18px] border border-slate-200 overflow-hidden">
                        <div class="divide-y divide-slate-200">
                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Reference Number</div>
                                <div class="text-sm font-semibold text-slate-900">{{ receipt.reference_number }}</div>
                            </div>
                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Transaction Date</div>
                                <div class="text-sm font-semibold text-slate-900">{{ receipt.transaction_date }}</div>
                            </div>
                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Destination Account</div>
                                <div class="text-sm font-semibold text-slate-900">{{ maskDestinationAccount(receipt.destination_account) }}</div>
                            </div>
                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Processing Fee</div>
                                <div class="text-sm font-semibold text-slate-900">₱{{ receipt.processing_fee.toFixed(2) }}</div>
                            </div>
                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</div>
                                <div class="text-sm font-semibold text-slate-900">{{ receipt.status }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Remarks</div>
                        <div class="mt-4 rounded-[18px] bg-slate-50 border border-slate-200 p-5 text-sm text-slate-700">
                            {{ receipt.remarks }}
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 sm:p-8">
                    <div class="text-sm text-slate-600 leading-relaxed">
                        This electronic receipt serves as proof of transaction processed by Morrisons Commercial & General Merchandise Co. Please keep this copy for your records and future reference.
                    </div>
                    <div class="mt-5 flex items-center gap-3 text-sm font-semibold text-slate-900">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        Secured & Encrypted Transaction
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const page = usePage();
const form = useForm({
    recipient_email: '',
    amount: '',
});
const showReceiptModal = ref(false);
const receipt = computed(() => page.props.flash?.transfer_receipt ?? null);

const currency = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    maximumFractionDigits: 2,
});

const formattedAmount = computed(() => {
    if (!receipt.value) {
        return '';
    }
    return currency.format((receipt.value.amount_cents ?? 0) / 100);
});

const openReceiptModal = () => {
    showReceiptModal.value = true;
};

watch(receipt, (value) => {
    if (value) {
        openReceiptModal();
    }
});

onMounted(() => {
    if (receipt.value) {
        openReceiptModal();
    }
});

const maskDestinationAccount = (account) => {
    if (!account || typeof account !== 'string') {
        return '';
    }

    if (account.includes('@')) {
        const [local, domain] = account.split('@');
        const maskedLocal = local.length > 3 ? `${local.slice(0, 2)}****${local.slice(-1)}` : '****';
        return `${maskedLocal}@${domain}`;
    }

    const digits = account.replace(/\D/g, '');
    if (digits.length <= 4) {
        return '****';
    }

    return `**** **** ${digits.slice(-4)}`;
};

const closeReceiptModal = () => {
    showReceiptModal.value = false;
};

const submitTransfer = () => {
    form.post(route('transfer.store', {}, false));
};
</script>
