<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const showSidebar = ref(false);
const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.roles?.includes('admin'));
const adminNotifications = computed(() => page.props.admin_notifications ?? {});
const pendingWithdrawals = computed(() => adminNotifications.value.pending_withdrawals ?? 0);
const pendingDeposits = computed(() => adminNotifications.value.pending_deposits ?? 0);
const referralUsername = computed(() => page.props.auth?.user?.name ?? '');
const referralLink = computed(() =>
    referralUsername.value ? route('register.referral', referralUsername.value) : ''
);

const copyReferralLink = async () => {
    if (!referralLink.value) {
        return;
    }

    try {
        await navigator.clipboard.writeText(referralLink.value);
    } catch (error) {
        // ignore clipboard errors
    }
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-[#f4f6f4]">
            <nav class="relative overflow-hidden bg-emerald-800 border-b border-emerald-900/20">
                <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-emerald-900/40"></div>
                <div class="pointer-events-none absolute right-10 top-6 h-28 w-40 -rotate-12 rounded-full bg-emerald-900/45"></div>
                <div class="pointer-events-none absolute left-1/2 top-8 h-16 w-24 -translate-x-1/2 -rotate-12 rounded-full bg-amber-400/90"></div>
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between min-h-[6rem]">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center gap-3">
                                <button type="button" class="flex items-center gap-3" @click="showSidebar = true">
                                    <ApplicationLogo class="block h-9 w-auto" />
                                    <div class="flex flex-col justify-center leading-none">
                                        <div class="text-sm font-semibold text-white">Morrisons</div>
                                        <div class="text-[10px] tracking-[0.3em] text-amber-300">CORPORATE</div>
                                    </div>
                                </button>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden sm:-my-px sm:ms-10 sm:flex"></div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6"></div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-emerald-700 hover:text-emerald-900 hover:bg-emerald-50 focus:outline-none focus:bg-emerald-50 focus:text-emerald-900 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
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

    <div v-if="showSidebar" class="fixed inset-0 z-50">
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
                    :href="route('profile.edit')"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    @click="showSidebar = false"
                >
                    <span>Profile</span>
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
                <button
                    type="button"
                    class="flex w-full items-center justify-between rounded-xl border border-emerald-100 px-4 py-3 text-sm text-emerald-900 hover:bg-emerald-50"
                    :disabled="!referralLink"
                    @click="copyReferralLink"
                >
                    <span>Referral link</span>
                    <span class="text-xs text-emerald-700">Copy</span>
                </button>
            </div>
            <div class="border-t border-emerald-100 px-4 py-4">
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
</template>
