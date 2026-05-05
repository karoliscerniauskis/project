<template>
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center text-4xl">
            {{ icon }}
        </div>
        <h3 class="mt-4 text-lg font-medium text-slate-900">{{ title }}</h3>
        <p class="mt-2 text-slate-600">{{ description }}</p>
    </div>
</template>

<script setup lang="ts">
interface Props {
    title: string
    description?: string
    icon?: string
}

withDefaults(defineProps<Props>(), {
    description: '',
    icon: '📄',
})
</script>
