<template>
    <div>
        <h1>Create voucher</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else>
            <p v-if="provider !== null">
                Provider: <strong>{{ provider.name }}</strong>
            </p>

            <form @submit.prevent="onSubmit">
                <label>
                    Issued to email
                    <input v-model="issuedToEmail" type="email" autocomplete="email" required />
                </label>

                <button type="submit" :disabled="submitting">
                    {{ submitting ? 'Creating...' : 'Create voucher' }}
                </button>
            </form>

            <p v-if="successMessage">{{ successMessage }}</p>

            <p>
                <RouterLink :to="`/providers/${providerId}`">Back to provider</RouterLink>
            </p>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { getProvider, createVoucher, type ProviderView } from '@/api/provider.api'

const props = defineProps<{
    id: string
}>()

const router = useRouter()

const providerId = props.id
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const successMessage = ref('')
const provider = ref<ProviderView | null>(null)
const issuedToEmail = ref('')

onMounted(async () => {
    try {
        const response = await getProvider(providerId)
        provider.value = response.data
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load provider.'
    } finally {
        loading.value = false
    }
})

async function onSubmit(): Promise<void> {
    submitting.value = true
    error.value = ''
    successMessage.value = ''

    try {
        await createVoucher(providerId, {
            issuedToEmail: issuedToEmail.value,
        })

        successMessage.value = 'Voucher created successfully.'
        issuedToEmail.value = ''

        await router.push(`/providers/${providerId}`)
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to create voucher.'
    } finally {
        submitting.value = false
    }
}
</script>
