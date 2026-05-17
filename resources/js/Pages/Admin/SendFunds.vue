<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    user_id: props.users[0]?.id ?? null,
    amount: '',
});

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const selectedUser = computed(() => {
    return props.users.find((user) => user.id === Number(form.user_id)) || null;
});

const page = usePage();
const adminBalanceCents = computed(() => page.props.auth?.user?.balance_cents ?? 0);

const amountCents = computed(() => {
    const n = Number(form.amount);
    if (!n || Number.isNaN(n)) return 0;
    return Math.round(n * 100);
});

const insufficientFunds = computed(() => amountCents.value > adminBalanceCents.value);

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const submitSendFunds = () => {
    if (!form.user_id) {
        return;
    }

    form.post(route('admin.users.transfer', { user: form.user_id }), {
        preserveScroll: true,
        onSuccess: () => form.reset('amount'),
    });
};
</script>

<template>
    <Head title="Send Funds" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="mb-6">
                        <h1 class="text-xl font-semibold text-emerald-900">Send Funds</h1>
                        <p class="mt-2 text-sm text-slate-500">Transfer USD directly to a selected user from the admin portal.</p>
                    </div>

                    <form class="space-y-6" @submit.prevent="submitSendFunds">
                        <div>
                            <InputLabel for="user_id" value="Recipient user" />
                            <select
                                id="user_id"
                                v-model="form.user_id"
                                class="mt-1 block w-full rounded-md border border-emerald-100 bg-white px-3 py-2 text-sm text-emerald-900 focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option disabled value="">Select a user</option>
                                <option
                                    v-for="user in props.users"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name }} — {{ user.email }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <InputLabel for="amount" value="Amount (USD)" />
                            <TextInput
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.amount" />
                            <p class="mt-2 text-xs text-slate-500">Amount will be transferred immediately when submitted.</p>
                        </div>

                        <div v-if="selectedUser" class="rounded-2xl border border-emerald-100 bg-emerald-50/80 p-4 text-sm text-emerald-900">
                            <div class="font-semibold">Selected recipient</div>
                            <div class="mt-1">{{ selectedUser.name }} — {{ selectedUser.email }}</div>
                            <div class="text-slate-600">Balance: {{ formatCurrency(selectedUser.balance_cents) }}</div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <PrimaryButton type="submit" :disabled="form.processing || !form.user_id || !form.amount || insufficientFunds">
                                Send funds
                            </PrimaryButton>
                            <div v-if="insufficientFunds" class="text-xs text-rose-600">Insufficient admin funds: {{ formatCurrency(adminBalanceCents) }} available.</div>
                            <div v-if="form.recentlySuccessful" class="text-sm text-emerald-600">Funds sent successfully.</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
