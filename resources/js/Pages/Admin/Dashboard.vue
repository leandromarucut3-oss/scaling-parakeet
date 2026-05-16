<script setup>
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

// ... (ALL YOUR EXISTING SCRIPT STAYS EXACTLY THE SAME)
</script>

<template>
    <Head title="Admin" />

    <AuthenticatedLayout>

        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

                <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <div class="space-y-6">

                        <!-- USERS SECTION -->
                        <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">

                            <!-- HEADER (UPDATED WITH BACKUP BUTTON) -->
                            <div class="flex flex-wrap items-center justify-between gap-3">

                                <div>
                                    <div class="text-sm font-semibold text-emerald-800">Users</div>
                                    <div class="text-xs text-slate-500">
                                        {{ filteredUsers.length }} accounts
                                    </div>
                                </div>

                                <!-- ✅ BACKUP BUTTON ADDED HERE -->
                                <a
                                    href="/admin/backup"
                                    class="rounded-full bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700"
                                >
                                    Download Backup
                                </a>

                                <div class="w-full sm:w-64">
                                    <TextInput
                                        v-model="search"
                                        class="w-full"
                                        type="text"
                                        placeholder="Search name, email, ID"
                                    />
                                </div>
                            </div>

                            <!-- REST OF YOUR USERS TABLE (UNCHANGED) -->
                            <div class="mt-4 overflow-hidden rounded-2xl border border-emerald-100">
                                <div class="max-h-[420px] overflow-y-auto">
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
                                                class="cursor-pointer border-t border-emerald-100/60 transition hover:bg-emerald-50/70"
                                                :class="{ 'bg-emerald-100/60': user.id === selectedUserId }"
                                                @click="selectedUserId = user.id"
                                            >
                                                <td class="px-4 py-4">
                                                    <div class="font-semibold text-emerald-950">
                                                        {{ user.name }}
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        {{ user.email }}
                                                    </div>
                                                </td>

                                                <td class="px-4 py-4 text-xs text-emerald-800">
                                                    <span v-if="user.roles?.length">
                                                        {{ user.roles.join(', ') }}
                                                    </span>
                                                    <span v-else>User</span>
                                                </td>

                                                <td class="px-4 py-4">
                                                    {{ user.referrer?.name || '—' }}
                                                </td>

                                                <td class="px-4 py-4 text-xs text-slate-500">
                                                    {{ formatDate(user.created_at) }}
                                                </td>

                                                <td class="px-4 py-4 text-right font-semibold">
                                                    {{ formatCurrency(user.balance_cents) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- (REST OF YOUR PAGE REMAINS EXACTLY SAME) -->

                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>