<template>
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg
            class="mx-auto h-12 w-12 text-slate-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <slot name="icon">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
            </slot>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-slate-900">{{ title }}</h3>
        <p class="mt-2 text-slate-600">{{ description }}</p>
    </div>
</template>

<script setup lang="ts">
interface Props {
    title: string
    description?: string
}

withDefaults(defineProps<Props>(), {
    description: '',
})
</script>
