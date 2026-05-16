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

        <div v-if="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-slate-900/50"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="text-sm font-semibold text-emerald-900">Receipt</div>
                <div class="mt-4 text-sm text-slate-700">
                    {{ receiptMessage }}
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
const receiptMessage = computed(() => page.props.flash?.success ?? '');
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

watch(receiptMessage, (value) => {
    if (value) {
        openReceiptModal();
    }
});

onMounted(() => {
    if (receiptMessage.value) {
        openReceiptModal();
    }
});

const submitTransfer = () => {
    form.post(route('transfer.store', {}, false));
};
</script>
