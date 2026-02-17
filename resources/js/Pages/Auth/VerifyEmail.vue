<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="text-center">
            <h1 class="text-2xl font-semibold text-emerald-900">Verify Your Email</h1>
            <p class="mt-1 text-sm text-slate-500">
                Check your inbox for a verification link to activate your account.
            </p>
        </div>

        <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700" v-if="verificationLinkSent">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <PrimaryButton
                class="w-full justify-center rounded-md bg-emerald-800 py-3 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
            >
                Resend Verification Email
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full text-sm font-semibold text-emerald-800 hover:text-emerald-900"
            >
                Log Out
            </Link>

            <div class="pt-2 text-center text-xs text-slate-400">
                Any issues? Please contact investor.portal@morrisonsplc.co.uk
            </div>
        </form>
    </GuestLayout>
</template>
