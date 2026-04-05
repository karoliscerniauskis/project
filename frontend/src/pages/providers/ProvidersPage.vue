<template>
    <div>
        <h1>My providers</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else>
            <p v-if="providers.length === 0">
                You don't have any providers yet.
            </p>

            <ul v-else>
                <li v-for="provider in providers" :key="provider.id">
                    <template v-if="provider.status === 'active'">
                        <RouterLink :to="`/providers/${provider.id}`">
                            <strong>{{ provider.name }}</strong>
                        </RouterLink>
                    </template>
                    <template v-else>
                        <strong>{{ provider.name }}</strong>
                    </template>
                    — {{ provider.status }}
                </li>
            </ul>

            <p>
                <RouterLink to="/provider/create">Create provider</RouterLink>
            </p>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { getProviders, type ProviderView } from '@/api/provider.api'

const loading = ref(true)
const error = ref('')
const providers = ref<ProviderView[]>([])

onMounted(async () => {
    try {
        const response = await getProviders()
        providers.value = response.data
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load providers.'
    } finally {
        loading.value = false
    }
})
</script>
