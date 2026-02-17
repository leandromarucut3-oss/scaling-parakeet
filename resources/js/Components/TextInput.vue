<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="border-slate-200 bg-slate-50/70 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm"
        v-model="model"
        ref="input"
    />
</template>
