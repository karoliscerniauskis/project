<template>
    <div class="bg-red-50 border border-red-200 rounded-xl p-6">
        <p class="text-red-800">
            {{ message }}
        </p>
    </div>
</template>

<script setup lang="ts">
interface Props {
    message: string
}

defineProps<Props>()
</script>
