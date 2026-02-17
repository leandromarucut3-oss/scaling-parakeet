<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="text-center">
            <h1 class="text-2xl font-semibold text-emerald-900">Confirm Password</h1>
            <p class="mt-1 text-sm text-slate-500">
                Please confirm your password to continue.
            </p>
        </div>

        <form @submit.prevent="submit" class="mt-8 space-y-5">
            <div>
                <InputLabel for="password" value="Password" class="text-slate-700" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full rounded-md border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <PrimaryButton
                class="w-full justify-center rounded-md bg-emerald-800 py-3 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-900"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
            >
                Confirm
            </PrimaryButton>

            <div class="pt-2 text-center text-xs text-slate-400">
                Any issues? Please contact investor.portal@morrisonsplc.co.uk
            </div>
        </form>
    </GuestLayout>
</template>
