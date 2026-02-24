<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    referralLink: {
        type: String,
        default: '',
    },
    referralUsername: {
        type: String,
        default: '',
    },
    referrals: {
        type: Array,
        default: () => [],
    },
});

const copied = ref(false);

const shareLink = async () => {
    if (!props.referralLink) {
        return;
    }

    try {
        if (navigator.share) {
            await navigator.share({
                title: 'Join me',
                text: 'Use my referral link to register.',
                url: props.referralLink,
            });
            copied.value = true;
            setTimeout(() => {
                copied.value = false;
            }, 2000);
            return;
        }

        await navigator.clipboard.writeText(props.referralLink);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (error) {
        copied.value = false;
    }
};
</script>

<template>
    <Head title="Invites" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="text-sm font-semibold text-emerald-800">Referral link</div>
                    <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                        <div class="grid gap-2 text-xs text-emerald-900">
                            <div>
                                <span class="text-emerald-700">Username:</span>
                                <span class="font-semibold">{{ referralUsername || '—' }}</span>
                            </div>
                        </div>
                        <div class="rounded-lg border border-emerald-100 bg-white px-3 py-2 text-xs text-emerald-900 break-all">
                            {{ referralLink || 'Referral link is not available yet.' }}
                        </div>
                        <button
                            type="button"
                            class="mt-3 w-full rounded-lg border border-emerald-100 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-900 hover:bg-emerald-50"
                            :disabled="!referralLink"
                            @click="shareLink"
                        >
                            {{ copied ? 'Copied' : 'Share' }}
                        </button>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-semibold text-emerald-800">Direct downline</div>
                        <div class="text-xs text-slate-500">{{ referrals.length }} users</div>
                    </div>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-emerald-100">
                        <div class="max-h-[420px] overflow-y-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-emerald-50/80 text-emerald-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em]">Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="referral in referrals"
                                        :key="referral.id"
                                        class="border-t border-emerald-100/60"
                                    >
                                        <td class="px-4 py-4">
                                            <div class="font-semibold text-emerald-950">{{ referral.name }}</div>
                                            <div class="text-xs text-slate-500">{{ referral.email }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-sm font-semibold text-emerald-900">
                                            {{ referral.created_at || '—' }}
                                        </td>
                                    </tr>
                                    <tr v-if="!referrals.length">
                                        <td class="px-4 py-6 text-center text-sm text-slate-500" colspan="2">
                                            No direct referrals yet.
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
