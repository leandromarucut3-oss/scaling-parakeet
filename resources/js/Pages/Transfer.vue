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
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    recipient_email: '',
    amount: '',
});

const submitTransfer = () => {
    form.post(route('transfer.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
