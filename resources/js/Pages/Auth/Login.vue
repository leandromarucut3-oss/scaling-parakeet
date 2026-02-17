<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ status }}
        </div>

        <div class="text-center">
            <h1 class="text-2xl font-semibold text-emerald-900">Investor Portal</h1>
            <p class="mt-1 text-sm text-slate-500">Sign in to access your account</p>
        </div>

        <form @submit.prevent="submit" class="mt-8 space-y-5">
            <div>
                <InputLabel for="login" value="Email or username" class="text-slate-700" />

                <TextInput
                    id="login"
                    type="text"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.login"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@example.com or yourname"
                />

                <InputError class="mt-2" :message="form.errors.login" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-slate-700" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    Remember me
                </label>
            </div>

            <PrimaryButton
                class="w-full justify-center rounded-md bg-emerald-800 py-3 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
            >
                Login
            </PrimaryButton>

            <div class="flex flex-col items-center gap-2 text-xs text-slate-500 sm:flex-row sm:justify-between">
                <div>
                    Want to register your interest?
                    <Link :href="route('register')" class="font-semibold text-emerald-800 hover:text-emerald-900">
                        Click here
                    </Link>
                </div>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="font-semibold text-emerald-800 hover:text-emerald-900"
                >
                    Forgotten your password? Click here
                </Link>
            </div>

            <div class="pt-4 text-center text-xs text-slate-400">
                Any issues? Please contact investor.portal@morrisonsplc.co.uk
            </div>
        </form>
    </GuestLayout>
</template>
