<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
    ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-emerald-500 text-start text-base font-medium text-emerald-800 bg-emerald-50 focus:outline-none focus:text-emerald-900 focus:bg-emerald-100 focus:border-emerald-700 transition duration-150 ease-in-out'
    : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:text-emerald-800 hover:bg-emerald-50 hover:border-emerald-200 focus:outline-none focus:text-emerald-800 focus:bg-emerald-50 focus:border-emerald-200 transition duration-150 ease-in-out'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
