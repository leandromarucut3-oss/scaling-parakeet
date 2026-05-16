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

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    investor_name: props.contract?.investor_name ?? user.value?.name ?? '',
    civil_status: props.contract?.civil_status ?? '',
    complete_address: props.contract?.complete_address ?? '',
    id_type: props.contract?.id_type ?? '',
    id_number: props.contract?.id_number ?? '',
    id_date_issued: props.contract?.id_date_issued ?? '',
    signature_text: props.contract?.signature_text ?? '',
    signed_at: props.contract?.signed_at ? props.contract.signed_at.split('T')[0] : today,
    purchase_id: props.purchase?.id ?? null,
});

const previewSignedAt = computed(() => form.signed_at || today);

const planType = computed(() => props.purchase?.plan_name ?? 'Premiere Plan');
const durationDays = computed(() => props.purchase?.duration_days ?? '120');
const investmentAmount = computed(() => props.purchase ? (props.purchase.amount_cents / 100).toFixed(2) : '0.00');
const dailyRate = computed(() => props.purchase ? (props.purchase.daily_interest_bps / 100).toFixed(2) : '0.00');

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

                    <div v-if="props.contract" class="space-y-6">
                        <div class="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900">
                            You have already submitted your contract. Future purchases will automatically generate and email a new contract copy.
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 h-screen overflow-y-auto">
                            <h2 class="text-lg font-semibold text-slate-900 sticky top-0 bg-slate-50 pb-3">Your Submitted Contract</h2>

                            <div class="text-sm leading-relaxed text-slate-700 font-serif space-y-4">
                                <h3 class="text-center font-bold text-slate-900">MORRISONS PHILIPPINES</h3>
                                <h3 class="text-center font-bold text-slate-900">INVESTMENT PARTNERSHIP AGREEMENT</h3>

                                <p class="text-justify">
                                    This Partnership Agreement (the "Agreement") is executed on <strong>{{ props.contract.signed_at ? props.contract.signed_at.split('T')[0] : '' }}</strong> by and between:
                                </p>

                                <p class="text-justify">
                                    <strong>MORRISONS</strong>, a duly organized and existing corporation under the laws of the Republic of the Philippines, with principal business address at 18/20 Upper McKinley Bldg, McKinley Hill Taguig City, Philippines, 1634, represented herein by its Finance Officer, Mr. Jubert Undaya Yacup, hereinafter referred to as the "Company";
                                </p>

                                <p class="text-justify">
                                    <strong>{{ props.contract.investor_name }}</strong> of legal age, Filipina, <strong>{{ props.contract.civil_status }}</strong>, and a resident of <strong>{{ props.contract.complete_address }}</strong>, Philippines hereinafter referred to as the "Investor" or "Partner."
                                </p>

                                <p class="font-bold">WITNESSETH:</p>

                                <p class="text-justify">
                                    WHEREAS, the Company is engaged in providing investment opportunities through its Partnership Investment Program, which allows qualified investors to participate in its business operations by investing in specified plans with guaranteed daily interest returns;
                                </p>

                                <p class="text-justify">
                                    WHEREAS, the Company guarantees payment of daily interest income on investments, irrespective of prevailing economic conditions, market performance, or other external circumstances;
                                </p>

                                <p class="font-bold mt-4">INVESTOR INFORMATION</p>
                                <div class="text-justify space-y-2">
                                    <p>Investor Name: <strong>{{ props.contract.investor_name }}</strong></p>
                                    <p>Civil Status: <strong>{{ props.contract.civil_status }}</strong></p>
                                    <p>Address: <strong>{{ props.contract.complete_address }}</strong></p>
                                    <p>ID Type: <strong>{{ props.contract.id_type }}</strong></p>
                                    <p>ID Number: <strong>{{ props.contract.id_number }}</strong></p>
                                    <p>Date Issued: <strong>{{ props.contract.id_date_issued }}</strong></p>
                                </div>

                                <p class="font-bold mt-4">TERMS AND CONDITIONS</p>
                                <p class="text-justify">
                                    The Company shall pay the Investor a daily interest income based on the selected plan and investment amount. Interest shall be credited daily to the Investor's designated bank account. Account balances reflected on the Company's dashboard system shall be withdrawable to affiliated bank accounts, subject to a 5% processing fee.
                                </p>

                                <p class="font-bold mt-4">IN WITNESS WHEREOF</p>
                                <p class="text-justify">
                                    The parties hereunto affixed their signatures at Taguig City, Philippines.
                                </p>

                                <div class="mt-6 pt-6 border-t border-slate-300">
                                    <div class="grid grid-cols-2 gap-8">
                                        <div class="text-center">
                                            <p class="mt-12 pt-2 border-t border-slate-700 text-xs">Jubert Undaya Yacup<br>Finance Officer</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="mt-12 pt-2 border-t border-slate-700 text-xs">{{ props.contract.signature_text }}<br>Investor</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
                        <div class="space-y-6">
                            <div class="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900">
                                You can read the contract terms on the right while you fill in your information.
                            </div>

                            <form @submit.prevent="submit" class="space-y-6">
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

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm h-screen overflow-y-auto">
                            <h2 class="text-lg font-semibold text-slate-900 sticky top-0 bg-slate-50 pb-3">Contract Preview</h2>
                            
                            <div class="text-sm leading-relaxed text-slate-700 font-serif space-y-4">
                                <h3 class="text-center font-bold text-slate-900">MORRISONS PHILIPPINES</h3>
                                <h3 class="text-center font-bold text-slate-900">INVESTMENT PARTNERSHIP AGREEMENT</h3>

                                <p class="text-justify">
                                    This Partnership Agreement (the "Agreement") is executed on <strong>{{ previewSignedAt }}</strong> by and between:
                                </p>

                                <p class="text-justify">
                                    <strong>MORRISONS</strong>, a duly organized and existing corporation under the laws of the Republic of the Philippines, with principal business address at 18/20 Upper McKinley Bldg, McKinley Hill Taguig City, Philippines, 1634, represented herein by its Finance Officer, Mr. Jubert Undaya Yacup, hereinafter referred to as the "Company";
                                </p>

                                <p class="text-justify">
                                    <strong>{{ form.investor_name || 'Your Full Name' }}</strong> of legal age, Filipina, <strong>{{ form.civil_status || 'Civil Status' }}</strong>, and a resident of <strong>{{ form.complete_address || 'Your Address' }}</strong>, Philippines hereinafter referred to as the "Investor" or "Partner."
                                </p>

                                <p class="font-bold">WITNESSETH:</p>

                                <p class="text-justify">
                                    WHEREAS, the Company is engaged in providing investment opportunities through its Partnership Investment Program, which allows qualified investors to participate in its business operations by investing in specified plans with guaranteed daily interest returns;
                                </p>

                                <p class="text-justify">
                                    WHEREAS, the Company guarantees payment of daily interest income on investments, irrespective of prevailing economic conditions, market performance, or other external circumstances;
                                </p>

                                <p class="font-bold mt-4">1. INVESTMENT PACKAGE</p>
                                <div class="text-justify space-y-2">
                                    <p>Plan Type: <strong>{{ planType }}</strong></p>
                                    <p>Contract Term: <strong>{{ durationDays }} days</strong></p>
                                    <p>Investment Amount: <strong>P {{ investmentAmount }}</strong></p>
                                    <p>Daily Interest Rate: <strong>{{ dailyRate }}%</strong></p>
                                    <p>Commencement Date: <strong>{{ previewSignedAt }}</strong></p>
                                </div>

                                <p class="font-bold mt-4">2. TERM AND PAYMENT OF INTEREST</p>
                                <p class="text-justify">
                                    The Company shall pay the Investor a daily interest income based on the selected plan and investment amount. Interest shall be credited daily to the Investor's designated bank account.
                                </p>
                                <p class="text-justify">
                                    Account balances reflected on the Company's dashboard system shall be withdrawable to affiliated bank accounts, subject to a 5% processing fee.
                                </p>

                                <p class="font-bold mt-4">3. WITHDRAWAL OF CAPITAL</p>
                                <p class="text-justify">
                                    The Investor may request withdrawal of capital upon contract maturity. Released amounts shall be credited within three (3) banking days from the date of request.
                                </p>

                                <p class="font-bold mt-4">4. EARLY WITHDRAWAL OF CAPITAL</p>
                                <p class="text-justify">
                                    Should the Investor request an early withdrawal of capital prior to maturity, a ten percent (10%) penalty fee shall apply.
                                </p>

                                <p class="font-bold mt-4">5. CONFIDENTIALITY</p>
                                <p class="text-justify">
                                    Both the Company and the Investor agree to treat as confidential all business information, records, financial data, strategies, and other proprietary information acquired in relation to this Agreement.
                                </p>

                                <p class="font-bold mt-4">IN WITNESS WHEREOF</p>
                                <p class="text-justify">
                                    The parties hereunto affixed their signatures at Taguig City, Philippines.
                                </p>

                                <div class="mt-6 pt-6 border-t border-slate-300">
                                    <div class="grid grid-cols-2 gap-8">
                                        <div class="text-center">
                                            <p class="mt-12 pt-2 border-t border-slate-700 text-xs">Jubert Undaya Yacup<br>Finance Officer</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="mt-12 pt-2 border-t border-slate-700 text-xs">{{ form.signature_text || 'Investor Name' }}<br>Investor</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
