<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});

const packageOptions = [
    { key: 'premier', name: 'Premier', minAmount: 150, maxAmount: 799, dailyRate: 0.5, durationDays: 150 },
    { key: 'deluxe', name: 'Deluxe', minAmount: 800, maxAmount: 7999, dailyRate: 0.7, durationDays: 120 },
    { key: 'presidential', name: 'Presidential', minAmount: 8000, maxAmount: 1000000, dailyRate: 0.9, durationDays: 90 },
];

const form = useForm({
    user_id: props.users[0]?.id ?? null,
    plan_key: packageOptions[0].key,
    amount: '',
});

const selectedUser = computed(() => {
    return props.users.find((user) => user.id === Number(form.user_id)) || null;
});

const selectedPlan = computed(() => {
    return packageOptions.find((option) => option.key === form.plan_key) || packageOptions[0];
});

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const submitSendPackage = () => {
    if (!form.user_id) {
        return;
    }

    form.post(route('admin.users.grant-package', { user: form.user_id }), {
        preserveScroll: true,
        onSuccess: () => form.reset('amount'),
    });
};
</script>

<template>
    <Head title="Send Package" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="mb-6">
                        <h1 class="text-xl font-semibold text-emerald-900">Send Package</h1>
                        <p class="mt-2 text-sm text-slate-500">Grant a deposit package immediately to a selected user.</p>
                    </div>

                    <form class="space-y-6" @submit.prevent="submitSendPackage">
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
                            <InputLabel for="plan_key" value="Package" />
                            <select
                                id="plan_key"
                                v-model="form.plan_key"
                                class="mt-1 block w-full rounded-md border border-emerald-100 bg-white px-3 py-2 text-sm text-emerald-900 focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option
                                    v-for="option in packageOptions"
                                    :key="option.key"
                                    :value="option.key"
                                >
                                    {{ option.name }}
                                </option>
                            </select>
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 p-4 text-sm text-emerald-900">
                            <div class="font-semibold">Package details</div>
                            <div class="mt-2">Range: {{ formatCurrency(selectedPlan.minAmount * 100) }} - {{ formatCurrency(selectedPlan.maxAmount * 100) }}</div>
                            <div>Daily interest: {{ selectedPlan.dailyRate }}%</div>
                            <div>Duration: {{ selectedPlan.durationDays }} days</div>
                        </div>

                        <div>
                            <InputLabel for="amount" value="Amount (USD)" />
                            <TextInput
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                :min="selectedPlan.minAmount"
                                :max="selectedPlan.maxAmount"
                                class="mt-1 block w-full"
                            />
                            <p class="mt-2 text-xs text-slate-500">The amount must fit the selected package range.</p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <PrimaryButton type="submit" :disabled="form.processing || !form.user_id || !form.amount">
                                Grant package
                            </PrimaryButton>
                            <div v-if="form.recentlySuccessful" class="text-sm text-emerald-600">Package sent successfully.</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
