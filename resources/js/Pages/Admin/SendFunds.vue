<script setup>
import { computed, ref } from 'vue';
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

const searchQuery = ref('');
const filteredUsers = ref([]);
const showDropdown = ref(false);
const loading = ref(false);

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

const searchUsers = async (query) => {
    searchQuery.value = query;

    if (query.length < 2) {
        filteredUsers.value = [];
        showDropdown.value = false;
        return;
    }

    loading.value = true;
    try {
        const response = await fetch(`/admin/users/search?q=${encodeURIComponent(query)}`);
        filteredUsers.value = await response.json();
        showDropdown.value = filteredUsers.value.length > 0;
    } catch (error) {
        console.error('Search error:', error);
        filteredUsers.value = [];
    } finally {
        loading.value = false;
    }
};

const selectUser = (user) => {
    form.user_id = user.id;
    searchQuery.value = `${user.name} — ${user.email}`;
    showDropdown.value = false;
};

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
                            <InputLabel for="user_search" value="Recipient user" />
                            <div class="relative mt-1">
                                <TextInput
                                    id="user_search"
                                    :value="searchQuery"
                                    type="text"
                                    placeholder="Type name or email to search..."
                                    @input="searchUsers($event.target.value)"
                                    @focus="showDropdown = filteredUsers.length > 0"
                                    @blur="setTimeout(() => showDropdown = false, 200)"
                                    class="block w-full"
                                />

                                <div v-if="loading" class="absolute right-3 top-3 text-emerald-600">
                                    <span class="text-xs">Searching...</span>
                                </div>

                                <div v-if="showDropdown && filteredUsers.length > 0" class="absolute top-full left-0 right-0 mt-1 max-h-64 overflow-y-auto rounded-md border border-emerald-100 bg-white shadow-lg z-50">
                                    <button
                                        v-for="user in filteredUsers"
                                        :key="user.id"
                                        type="button"
                                        @click.prevent="selectUser(user)"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-emerald-50 focus:bg-emerald-50 focus:outline-none border-b border-emerald-50 last:border-b-0 transition"
                                    >
                                        <div class="font-medium text-emerald-900">{{ user.name }}</div>
                                        <div class="text-xs text-slate-600">{{ user.email }}</div>
                                        <div class="text-xs text-slate-500">Balance: {{ formatCurrency(user.balance_cents) }}</div>
                                    </button>
                                </div>
                            </div>
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
