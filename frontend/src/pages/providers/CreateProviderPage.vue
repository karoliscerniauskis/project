<template>
    <div>
        <h1>Create provider</h1>

        <form @submit.prevent="onSubmit">
            <label>
                Name
                <input v-model="name" type="text" required />
            </label>

            <button type="submit" :disabled="loading">
                {{ loading ? 'Creating...' : 'Create provider' }}
            </button>

            <p v-if="error">{{ error }}</p>
            <p v-if="success">{{ success }}</p>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { createProvider } from '@/api/provider.api'

const router = useRouter()

const name = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')

async function onSubmit() {
    loading.value = true
    error.value = ''
    success.value = ''

    try {
        await createProvider({ name: name.value })
        success.value = 'Provider created successfully.'
        await router.push('/providers')
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to create provider.'
    } finally {
        loading.value = false
    }
}
</script>
