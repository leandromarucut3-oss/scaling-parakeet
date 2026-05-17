<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    appUrl: {
        type: String,
        default: '',
    },
});

const search = ref('');

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2,
});

const dateFormatter = new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
});

const filteredUsers = computed(() => {
    const base = search.value
        ? props.users.filter((user) => {
            const needle = search.value.toLowerCase();

            return (
                user.name?.toLowerCase().includes(needle) ||
                user.email?.toLowerCase().includes(needle) ||
                String(user.id).includes(needle)
            );
        })
        : props.users;

    return [...base].sort((a, b) => {
        const aDate = a.created_at ? new Date(a.created_at).getTime() : 0;
        const bDate = b.created_at ? new Date(b.created_at).getTime() : 0;

        return bDate - aDate;
    });
});

const formatCurrency = (cents) => currency.format((cents ?? 0) / 100);

const formatDate = (value) => {
    if (!value) {
        return '—';
    }

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    return dateFormatter.format(parsed);
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-emerald-800">Users</div>
                            <div class="text-xs text-slate-500">{{ filteredUsers.length }} accounts</div>
                        </div>
                        <div class="w-full max-w-sm">
                            <TextInput
                                v-model="search"
                                class="w-full"
                                type="text"
                                placeholder="Search name, email, ID"
                            />
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-2xl border border-emerald-100">
                        <div class="max-h-[720px] overflow-y-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-emerald-50/80 text-emerald-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Role</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Referrer</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Joined</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em]">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="user in filteredUsers"
                                        :key="user.id"
                                        class="border-t border-emerald-100/60 hover:bg-emerald-50/70"
                                    >
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-emerald-950">{{ user.name }}</div>
                                            <div class="text-xs text-slate-500">{{ user.email }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-xs text-emerald-800">
                                            <span v-if="user.roles?.length" class="rounded-full bg-emerald-100 px-3 py-1">
                                                {{ user.roles.join(', ') }}
                                            </span>
                                            <span v-else class="text-slate-400">User</span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-emerald-950">
                                            {{ user.referrer?.name || '—' }}
                                            <div v-if="user.referrer?.email" class="text-xs text-slate-500">
                                                {{ user.referrer.email }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-xs text-slate-500">{{ formatDate(user.created_at) }}</td>
                                        <td class="px-4 py-4 text-right font-semibold text-emerald-950">{{ formatCurrency(user.balance_cents) }}</td>
                                    </tr>
                                    <tr v-if="!filteredUsers.length">
                                        <td class="px-4 py-6 text-center text-sm text-slate-500" colspan="5">
                                            No users match this search.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
