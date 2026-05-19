<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const showSidebar = ref(false);
const showFranchiseModal = ref(false);
const referralShared = ref(false);
const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.roles?.includes('admin'));
const adminNotifications = computed(() => page.props.admin_notifications ?? {});
const pendingWithdrawals = computed(() => adminNotifications.value.pending_withdrawals ?? 0);
const pendingDeposits = computed(() => adminNotifications.value.pending_deposits ?? 0);
const referralUsername = computed(() => page.props.auth?.user?.name ?? '');
const referralCode = computed(() => page.props.auth?.user?.referral_code ?? '');
const referralLink = computed(() =>
    referralUsername.value ? route('register.referral', referralUsername.value) : ''
);

const franchiseForm = useForm({
    name: '',
    email: '',
    phone: '',
    target_location: '',
    business_plan: '',
});

const copyReferralLink = async () => {
    if (!referralLink.value) {
        return;
    }

    try {
        if (navigator.share) {
            await navigator.share({
                title: 'Join me',
                text: 'Use my referral link to register.',
                url: referralLink.value,
            });
            referralShared.value = true;
            setTimeout(() => {
                referralShared.value = false;
            }, 2000);
            return;
        }

        await navigator.clipboard.writeText(referralLink.value);
        referralShared.value = true;
        setTimeout(() => {
            referralShared.value = false;
        }, 2000);
    } catch (error) {
        // ignore clipboard errors
    }
};

const submitFranchiseApplication = () => {
    franchiseForm.post(route('franchise.apply'), {
        onSuccess: () => {
            franchiseForm.reset();
            showFranchiseModal.value = false;
        },
    });
};
</script>

<template>
    <div>
        <div :class="['min-h-screen bg-[#f4f6f4]', isAdmin ? 'ml-72' : '']">
            <nav class="relative overflow-hidden bg-emerald-800 border-b border-emerald-900/20">
                <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-emerald-900/40"></div>
                <div class="pointer-events-none absolute right-10 top-6 h-28 w-40 -rotate-12 rounded-full bg-emerald-900/45"></div>
                <div class="pointer-events-none absolute left-1/2 top-8 h-16 w-24 -translate-x-1/2 -rotate-12 rounded-full bg-amber-400/90"></div>
                <div class="absolute right-6 top-4 z-20 hidden sm:block">
                    <a
                        href="https://www.morrisons-corporate.com/About-us/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="rounded-full border border-emerald-700/40 bg-emerald-900/30 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-50 transition hover:bg-emerald-900/40"
                    >
                        About us
                    </a>
                </div>
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between min-h-[6rem]">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center gap-3">
                                <button
                                    v-if="!isAdmin"
                                    type="button"
                                    class="flex items-center gap-3 rounded-full border border-emerald-700/40 bg-emerald-900/20 px-4 py-2 text-emerald-50 transition hover:bg-emerald-900/30"
                                    @click="showSidebar = true"
                                    aria-label="Open menu"
                                >
                                    <span class="flex w-5 flex-col gap-1">
                                        <span class="h-0.5 w-full rounded-full bg-emerald-50"></span>
                                        <span class="h-0.5 w-full rounded-full bg-emerald-50"></span>
                                        <span class="h-0.5 w-full rounded-full bg-emerald-50"></span>
                                    </span>
                                    <span class="text-xs font-semibold uppercase tracking-[0.2em]">Menu</span>
                                </button>
                                <a
                                    v-else
                                    :href="route('admin.backup.download')"
                                    class="rounded-full border border-emerald-700/40 bg-emerald-900/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-50 transition hover:bg-emerald-900/30"
                                    aria-label="Run manual backup"
                                >
                                    Backup
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden sm:-my-px sm:ms-10 sm:flex"></div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6"></div>

                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                ></div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white/95 border-b border-emerald-100 shadow-sm" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>

        <!-- Permanent admin sidebar (fixed) -->
        <aside v-if="isAdmin" class="fixed left-0 top-0 z-40 h-screen w-72 flex-col bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-emerald-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 10-16 0" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-emerald-900">{{ $page.props.auth.user.name }}</div>
                        <div class="text-xs text-emerald-700/80">{{ $page.props.auth.user.email }}</div>
                    </div>
                </div>
            </div>
            <div class="flex-1 space-y-2 px-4 py-4">
                <Link
                    :href="isAdmin ? route('admin.dashboard') : route('dashboard')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Dashboard</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="!isAdmin"
                    :href="route('profile.edit')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Profile</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="!isAdmin"
                    :href="route('invites')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Associates</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    :href="isAdmin ? route('admin.deposits') : route('shares.buy')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span class="flex items-center gap-2">
                        <span>Deposits</span>
                        <span
                            v-if="isAdmin && pendingDeposits > 0"
                            class="inline-flex items-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-white"
                        >
                            {{ pendingDeposits }}
                        </span>
                    </span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    :href="isAdmin ? route('admin.withdrawals') : route('dashboard')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span class="flex items-center gap-2">
                        <span>Withdrawal</span>
                        <span
                            v-if="isAdmin && pendingWithdrawals > 0"
                            class="inline-flex items-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-white"
                        >
                            {{ pendingWithdrawals }}
                        </span>
                    </span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="isAdmin"
                    :href="route('admin.send-funds')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Send funds</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="isAdmin"
                    :href="route('admin.send-package')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Send package</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="isAdmin"
                    :href="route('admin.recent-transactions')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Recent transactions</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
            </div>
            <div class="border-t border-emerald-100 px-4 py-4 space-y-3">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                >
                    <span>Log out</span>
                    <span class="text-xs text-emerald-700">Exit</span>
                </Link>
            </div>
        </aside>

        <!-- Modal sidebar for non-admin (mobile/overlay) -->
        <div v-if="showSidebar && !isAdmin" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-slate-900/40" @click="showSidebar = false"></div>
            <aside class="relative flex h-full w-72 flex-col bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-emerald-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 10-16 0" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-emerald-900">{{ $page.props.auth.user.name }}</div>
                        <div class="text-xs text-emerald-700/80">{{ $page.props.auth.user.email }}</div>
                    </div>
                </div>
                <button
                    type="button"
                    class="text-xs font-semibold uppercase tracking-widest text-emerald-700"
                    @click="showSidebar = false"
                >
                    Close
                </button>
            </div>
            <div class="flex-1 space-y-2 px-4 py-4">
                <Link
                    :href="isAdmin ? route('admin.dashboard') : route('dashboard')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Dashboard</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="!isAdmin"
                    :href="route('profile.edit')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Profile</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="!isAdmin"
                    :href="route('invites')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Associates</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    :href="isAdmin ? route('admin.deposits') : route('shares.buy')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Deposits</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    :href="isAdmin ? route('admin.withdrawals') : route('dashboard')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Withdrawal</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="isAdmin"
                    :href="route('admin.send-funds')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Send funds</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="isAdmin"
                    :href="route('admin.send-package')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Send package</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <Link
                    v-if="isAdmin"
                    :href="route('admin.recent-transactions')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Recent transactions</span>
                    <span class="text-xs text-emerald-700">Go</span>
                </Link>
                <button
                    v-if="!isAdmin"
                    type="button"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false; showFranchiseModal = true"
                >
                    <span>Franchise Application</span>
                    <span class="text-xs text-emerald-700">Apply</span>
                </button>
            </div>
            <div class="border-t border-emerald-100 px-4 py-4 space-y-3">
                <div v-if="!isAdmin" class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Referral link</div>
                    <div class="mt-2 grid gap-1 text-[11px] text-emerald-900">
                        <div>
                            <span class="text-emerald-700">Username:</span>
                            <span class="font-semibold">{{ referralUsername || '—' }}</span>
                        </div>
                    </div>
                    <div class="mt-2 rounded-lg border border-emerald-100 bg-white px-3 py-2 text-[11px] text-emerald-900 break-all">
                        {{ referralLink || 'Referral link is not available yet.' }}
                    </div>
                    <button
                        type="button"
                        class="mt-3 w-full rounded-lg border border-emerald-100 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-900 hover:bg-emerald-50"
                        :disabled="!referralLink"
                        @click="copyReferralLink"
                    >
                        {{ referralShared ? 'Shared' : 'Share' }}
                    </button>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                >
                    <span>Log out</span>
                    <span class="text-xs text-emerald-700">Exit</span>
                </Link>
            </div>
        </aside>
    </div>

    <!-- Franchise Application Modal -->
    <div v-if="showFranchiseModal" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/50" @click="showFranchiseModal = false"></div>
        <div class="relative flex min-h-screen items-center justify-center px-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div class="text-sm font-semibold text-emerald-900">Franchise Application</div>
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-emerald-100 text-emerald-800 hover:bg-emerald-50"
                        @click="showFranchiseModal = false"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="submitFranchiseApplication" class="mt-4 space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-emerald-900">Full Name</label>
                        <input
                            id="name"
                            v-model="franchiseForm.name"
                            type="text"
                            class="mt-1 block w-full rounded-lg border border-emerald-100 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            required
                        />
                        <div v-if="franchiseForm.errors.name" class="mt-2 text-xs text-rose-600">
                            {{ franchiseForm.errors.name }}
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-emerald-900">Email</label>
                        <input
                            id="email"
                            v-model="franchiseForm.email"
                            type="email"
                            class="mt-1 block w-full rounded-lg border border-emerald-100 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            required
                        />
                        <div v-if="franchiseForm.errors.email" class="mt-2 text-xs text-rose-600">
                            {{ franchiseForm.errors.email }}
                        </div>
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-emerald-900">Phone Number</label>
                        <input
                            id="phone"
                            v-model="franchiseForm.phone"
                            type="tel"
                            class="mt-1 block w-full rounded-lg border border-emerald-100 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            required
                        />
                        <div v-if="franchiseForm.errors.phone" class="mt-2 text-xs text-rose-600">
                            {{ franchiseForm.errors.phone }}
                        </div>
                    </div>
                    <div>
                        <label for="target_location" class="block text-sm font-medium text-emerald-900">Target Location</label>
                        <input
                            id="target_location"
                            v-model="franchiseForm.target_location"
                            type="text"
                            class="mt-1 block w-full rounded-lg border border-emerald-100 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="City, State/Country"
                            required
                        />
                        <div v-if="franchiseForm.errors.target_location" class="mt-2 text-xs text-rose-600">
                            {{ franchiseForm.errors.target_location }}
                        </div>
                    </div>
                    <div>
                        <label for="business_plan" class="block text-sm font-medium text-emerald-900">Business Plan</label>
                        <textarea
                            id="business_plan"
                            v-model="franchiseForm.business_plan"
                            rows="4"
                            class="mt-1 block w-full rounded-lg border border-emerald-100 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Describe your business plan..."
                            required
                        ></textarea>
                        <div v-if="franchiseForm.errors.business_plan" class="mt-2 text-xs text-rose-600">
                            {{ franchiseForm.errors.business_plan }}
                        </div>
                    </div>
                    <div class="flex items-center justify-end">
                        <button
                            type="button"
                            class="mr-3 rounded-lg border border-emerald-100 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-900 hover:bg-emerald-50"
                            @click="showFranchiseModal = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-emerald-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                            :disabled="franchiseForm.processing"
                        >
                            Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
