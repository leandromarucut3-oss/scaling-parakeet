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

        <!-- Receipt Modal -->
        <div v-if="showReceiptModal && receipt" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50" @click.self="closeReceiptModal">
            <div class="flex items-center justify-center p-4">
                <div class="relative w-full max-w-2xl">
                    <button
                        type="button"
                        class="absolute -top-10 right-0 text-white hover:text-gray-200 text-2xl font-light z-60"
                        @click="closeReceiptModal"
                        aria-label="Close receipt"
                    >
                        ×
                    </button>

                    <div class="receipt-container" ref="modalContainer">
                        <!-- Header -->
                        <div class="header">
                            <div class="brand">
                                <h1>MORRISONS</h1>
                                <div class="status-badge">
                                    ✓ Transfer Successfully Completed
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="content">
                            <div class="section-title">Fund Transfer Receipt</div>

                            <div class="amount-box">
                                <div class="amount-label">Transfer Amount</div>
                                <div class="amount">{{ formattedAmount }}</div>
                            </div>

                            <div class="details-grid">
                                <div class="detail-card">
                                    <div class="detail-label">Sender</div>
                                    <div class="detail-value">
                                        {{ receipt.sender_name }}
                                    </div>
                                </div>

                                <div class="detail-card">
                                    <div class="detail-label">Recipient</div>
                                    <div class="detail-value">
                                        {{ receipt.recipient_name }}
                                    </div>
                                </div>
                            </div>

                            <div class="transfer-info">
                                <div class="info-row">
                                    <div class="info-title">Reference Number</div>
                                    <div class="info-value">{{ receipt.reference_number }}</div>
                                </div>

                                <div class="info-row">
                                    <div class="info-title">Transaction Date</div>
                                    <div class="info-value">{{ receipt.transaction_date }}</div>
                                </div>

                                <div class="info-row">
                                    <div class="info-title">Destination Account</div>
                                    <div class="info-value">{{ maskDestinationAccount(receipt.destination_account) }}</div>
                                </div>

                                <div class="info-row">
                                    <div class="info-title">Processing Fee</div>
                                    <div class="info-value">{{ currency.format(receipt.processing_fee) }}</div>
                                </div>

                                <div class="info-row">
                                    <div class="info-title">Status</div>
                                    <div class="info-value">{{ receipt.status }}</div>
                                </div>
                            </div>

                            <div class="section-title mt-8">Remarks</div>
                            <div class="detail-card">
                                <div class="detail-value remarks-text">
                                    {{ receipt.remarks }}
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="footer">
                            <div class="footer-note">
                                This electronic receipt serves as proof of transaction. Please keep this copy for your records and future reference.
                            </div>

                            <div class="security">
                                <div class="dot"></div>
                                Secured & Encrypted Transaction
                            </div>

                            <div class="mt-4 flex justify-end">
                                <PrimaryButton type="button" @click="closeReceiptModal">Close</PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.receipt-container {
    background: #ffffff;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
    border: 1px solid #dbe3ee;
}

.header {
    background: linear-gradient(135deg, #0f2b46 0%, #12395d 50%, #1b4f7a 100%);
    padding: 38px 42px;
    color: #ffffff;
    position: relative;
}

.header::after {
    content: "";
    position: absolute;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    top: -60px;
    right: -40px;
}

.brand {
    position: relative;
    z-index: 2;
}

.brand h1 {
    font-size: 30px;
    font-weight: 700;
    letter-spacing: 1px;
}

.brand p {
    margin-top: 8px;
    font-size: 14px;
    opacity: 0.85;
}

.status-badge {
    margin-top: 22px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.15);
    padding: 10px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    backdrop-filter: blur(8px);
}

.content {
    padding: 38px 42px;
}

.section-title {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6b7280;
    margin-bottom: 20px;
    font-weight: 700;
}

.amount-box {
    background: linear-gradient(135deg, #f5f8fc, #edf3fb);
    border: 1px solid #d9e4f2;
    border-radius: 18px;
    padding: 28px;
    margin-bottom: 32px;
}

.amount-label {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 10px;
}

.amount {
    font-size: 42px;
    font-weight: 700;
    color: #0f2b46;
    letter-spacing: -1px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 18px;
    margin-bottom: 34px;
}

.detail-card {
    background: #f9fbfd;
    border: 1px solid #e4ebf4;
    border-radius: 16px;
    padding: 20px;
}

.detail-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 10px;
    font-weight: 600;
}

.detail-value {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    line-height: 1.5;
}

.remarks-text {
    font-weight: 500;
    color: #374151;
}

.transfer-info {
    border: 1px solid #e5ebf3;
    border-radius: 18px;
    overflow: hidden;
    margin-bottom: 34px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #edf2f7;
    gap: 20px;
}

.info-row:last-child {
    border-bottom: none;
}

.info-title {
    color: #6b7280;
    font-size: 14px;
}

.info-value {
    font-size: 15px;
    font-weight: 600;
    text-align: right;
    color: #111827;
}

.footer {
    padding: 28px 42px 36px;
    background: #f8fafc;
    border-top: 1px solid #e7edf5;
}

.footer-note {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.7;
}

.security {
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #0f2b46;
    font-weight: 600;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #22c55e;
}

@media (max-width: 640px) {
    .header,
    .content,
    .footer {
        padding: 28px 22px;
    }

    .amount {
        font-size: 34px;
    }

    .info-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-value {
        text-align: left;
    }

    .receipt-container {
        width: calc(100vw - 24px);
        max-height: calc(100vh - 24px);
        border-radius: 12px;
        overflow: hidden;
    }

    .brand h1 {
        font-size: 18px;
    }

    .amount {
        font-size: 28px;
    }

    .detail-value {
        font-size: 14px;
    }

    .section-title {
        font-size: 11px;
    }
}

.mt-8 {
    margin-top: 32px;
}
</style>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue';

const page = usePage();
const form = useForm({
    recipient_email: '',
    amount: '',
});
const showReceiptModal = ref(false);
const modalContainer = ref(null);
const receipt = computed(() => {
    const flashData = page.props.flash?.transfer_receipt;
    console.log('Flash data:', page.props.flash);
    console.log('Receipt data:', flashData);
    return flashData ?? null;
});

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const formattedAmount = computed(() => {
    if (!receipt.value) {
        return '';
    }
    return currency.format((receipt.value.amount_cents ?? 0) / 100);
});

const openReceiptModal = () => {
    console.log('Opening receipt modal with data:', receipt.value);
    showReceiptModal.value = true;
};

const onDocumentClick = (e) => {
    if (!showReceiptModal.value) return;
    const container = modalContainer.value;
    if (!container) return;
    const target = e.target;
    if (target instanceof Node && !container.contains(target)) {
        closeReceiptModal();
    }
};

watch(receipt, (value) => {
    console.log('Receipt changed to:', value);
    if (value) {
        setTimeout(() => {
            openReceiptModal();
        }, 100);
    }
}, { immediate: true });

watch(() => page.props.flash, (flashData) => {
    console.log('Page props flash changed:', flashData);
    if (flashData?.transfer_receipt) {
        setTimeout(() => {
            openReceiptModal();
        }, 100);
    }
}, { deep: true });

onMounted(() => {
    console.log('Component mounted, receipt:', receipt.value);
    if (receipt.value) {
        setTimeout(() => {
            openReceiptModal();
        }, 100);
    }
    document.addEventListener('click', onDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
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
