<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    referralCode: {
        type: String,
        default: '',
    },
    referrerName: {
        type: String,
        default: '',
    },
});

const form = useForm({
    name: '',
    email: '',
    referral_code: props.referralCode,
    referrer_name: props.referrerName,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="text-center">
            <h1 class="text-2xl font-semibold text-emerald-900">Register Interest</h1>
            <p class="mt-1 text-sm text-slate-500">Create your investor portal account</p>
        </div>

        <form @submit.prevent="submit" class="mt-8 space-y-5">
            <div>
                <InputLabel for="name" value="Name" class="text-slate-700" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" class="text-slate-700" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="referral_code" value="Referral code" class="text-slate-700" />

                <TextInput
                    id="referral_code"
                    type="text"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.referral_code"
                    autocomplete="off"
                    placeholder="e.g. AB12CD34EF"
                />

                <InputError class="mt-2" :message="form.errors.referral_code" />
            </div>

            <div>
                <InputLabel for="referrer_name" value="Inviter username" class="text-slate-700" />

                <TextInput
                    id="referrer_name"
                    type="text"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.referrer_name"
                    autocomplete="off"
                    placeholder="their username"
                />

                <InputError class="mt-2" :message="form.errors.referrer_name" />

                <p class="mt-2 text-xs text-slate-500">Provide a referral code or inviter username to continue.</p>
            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-slate-700" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" class="text-slate-700" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <PrimaryButton
                class="w-full justify-center rounded-md bg-emerald-800 py-3 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
            >
                Register
            </PrimaryButton>

            <div class="text-center text-xs text-slate-500">
                Already registered?
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
