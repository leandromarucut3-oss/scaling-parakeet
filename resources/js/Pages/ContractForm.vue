<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const props = defineProps({
    contract: {
        type: Object,
        default: () => null,
    },
    purchase: {
        type: Object,
        default: () => null,
    },
});

const user = computed(() => page.props.auth?.user);

const form = useForm({
    investor_name: props.contract?.investor_name ?? user.value?.name ?? '',
    civil_status: props.contract?.civil_status ?? '',
    complete_address: props.contract?.complete_address ?? '',
    id_type: props.contract?.id_type ?? '',
    id_number: props.contract?.id_number ?? '',
    id_date_issued: props.contract?.id_date_issued ?? '',
    signature_text: props.contract?.signature_text ?? '',
    signed_at: props.contract?.signed_at ? props.contract.signed_at.split('T')[0] : new Date().toISOString().split('T')[0],
    purchase_id: props.purchase?.id ?? null,
});

const submit = () => {
    form.post(route('contract.store'));
};
</script>

<template>
    <Head title="Contract Submission" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-xl font-semibold text-emerald-900">Investment Contract</h1>
                            <p class="text-sm text-slate-500">Complete this contract once so it can be reused for future package purchases.</p>
                        </div>
                    </div>

                    <div v-if="props.contract">
                        <div class="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900">
                            You have already submitted your contract. Future purchases will automatically generate and email a new contract copy.
                        </div>
                    </div>

                    <form v-else @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Investor Full Name</span>
                                <input
                                    v-model="form.investor_name"
                                    type="text"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                />
                                <p v-if="form.errors.investor_name" class="mt-1 text-xs text-rose-600">{{ form.errors.investor_name }}</p>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Civil Status</span>
                                <input
                                    v-model="form.civil_status"
                                    type="text"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                />
                            </label>
                        </div>

                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Complete Address</span>
                            <textarea
                                v-model="form.complete_address"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                rows="3"
                            ></textarea>
                        </label>

                        <div class="grid gap-6 md:grid-cols-3">
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">ID Type</span>
                                <input
                                    v-model="form.id_type"
                                    type="text"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                />
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">ID Number</span>
                                <input
                                    v-model="form.id_number"
                                    type="text"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                />
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Date Issued</span>
                                <input
                                    v-model="form.id_date_issued"
                                    type="date"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                />
                            </label>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Signature (Type full name)</span>
                                <input
                                    v-model="form.signature_text"
                                    type="text"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                />
                                <p v-if="form.errors.signature_text" class="mt-1 text-xs text-rose-600">{{ form.errors.signature_text }}</p>
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Signing Date</span>
                                <input
                                    v-model="form.signed_at"
                                    type="date"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                />
                                <p v-if="form.errors.signed_at" class="mt-1 text-xs text-rose-600">{{ form.errors.signed_at }}</p>
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-full bg-emerald-900 px-6 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-emerald-800"
                            :disabled="form.processing"
                        >
                            Submit Contract
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
