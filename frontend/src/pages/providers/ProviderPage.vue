<template>
    <div>
        <h1>Provider</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else-if="provider">
            <h2>{{ provider.name }}</h2>
            <p>Status: {{ provider.status }}</p>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { getProvider, type ProviderView } from '@/api/provider.api'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const provider = ref<ProviderView | null>(null)

onMounted(async () => {
    const id = route.params.id

    if (typeof id !== 'string' || id.length === 0) {
        loading.value = false
        error.value = 'Invalid provider id.'
        return
    }

    try {
        const response = await getProvider(id)
        provider.value = response.data
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load provider.'
    } finally {
        loading.value = false
    }
})
</script>
