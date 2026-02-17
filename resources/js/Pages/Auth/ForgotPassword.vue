<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="text-center">
            <h1 class="text-2xl font-semibold text-emerald-900">Reset Password</h1>
            <p class="mt-1 text-sm text-slate-500">
                Enter your email address and we will send you a reset link.
            </p>
        </div>

        <div v-if="status" class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="mt-6 space-y-5">
            <div>
                <InputLabel for="email" value="Email" class="text-slate-700" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <PrimaryButton
                class="w-full justify-center rounded-md bg-emerald-800 py-3 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
            >
                Email Password Reset Link
            </PrimaryButton>

            <div class="text-center text-xs text-slate-500">
                Remember your password?
                <Link :href="route('login')" class="font-semibold text-emerald-800 hover:text-emerald-900">
                    Sign in
                </Link>
            </div>

            <div class="pt-2 text-center text-xs text-slate-400">
                Any issues? Please contact investor.portal@morrisonsplc.co.uk
            </div>
        </form>
    </GuestLayout>
</template>
