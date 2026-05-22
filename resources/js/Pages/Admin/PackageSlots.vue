<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    packages: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    slots: Object.fromEntries(
        Object.entries(props.packages).map(([planKey, plan]) => [planKey, plan.remaining_slots ?? 0])
    ),
});

const page = usePage();

const successMessage = computed(() => {
    return form.recentlySuccessful ? 'Slots updated successfully.' : page.props.flash?.success ?? '';
});

const errorMessage = computed(() => {
    const errors = Object.values(form.errors);
    if (errors.length === 0) {
        return page.props.flash?.error ?? '';
    }

    const firstError = errors.find((value) => value);
    return Array.isArray(firstError) ? firstError[0] : firstError;
});

const packageRows = computed(() =>
    Object.values(props.packages).map((plan) => ({
        ...plan,
        remaining_slots: plan.remaining_slots ?? 0,
        slot_capacity: plan.slot_capacity ?? 0,
        taken_slots: plan.taken_slots ?? 0,
    }))
);

const submitSlots = () => {
    form.post(route('admin.package-slots.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Available slots" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl bg-white/95 p-6 shadow-lg ring-1 ring-emerald-100">
                    <div class="mb-6">
                        <h1 class="text-xl font-semibold text-emerald-900">Available slots</h1>
                        <p class="mt-2 text-sm text-slate-500">Update how many slots remain for each package. This setting will be used by the customer purchase flow.</p>
                    </div>

                    <div v-if="successMessage" class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ successMessage }}
                    </div>
                    <div v-if="!successMessage && errorMessage" class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        {{ errorMessage }}
                    </div>

                    <form class="space-y-6" @submit.prevent="submitSlots">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div
                                v-for="plan in packageRows"
                                :key="plan.plan_key"
                                class="rounded-3xl border border-emerald-100 bg-emerald-50/70 p-4"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-emerald-900">{{ plan.name }}</div>
                                        <div class="mt-1 text-xs text-slate-600">Key: {{ plan.plan_key }}</div>
                                    </div>
                                    <div class="text-right text-xs text-slate-500">
                                        <div>Capacity {{ plan.slot_capacity }}</div>
                                        <div>Taken {{ plan.taken_slots }}</div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <InputLabel :for="`slots.${plan.plan_key}`" value="Remaining slots" />
                                    <TextInput
                                        :id="`slots.${plan.plan_key}`"
                                        v-model.number="form.slots[plan.plan_key]"
                                        type="number"
                                        min="0"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors[plan.plan_key]" />
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                Update slots
                            </PrimaryButton>
                            <div v-if="form.recentlySuccessful" class="text-sm text-emerald-600">Slots updated successfully.</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
