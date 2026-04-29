<template>
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <div
            class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"
        ></div>
        <p class="mt-4 text-slate-600">{{ message }}</p>
    </div>
</template>

<script setup lang="ts">
interface Props {
    message?: string
}

withDefaults(defineProps<Props>(), {
    message: 'Loading...',
})
</script>
