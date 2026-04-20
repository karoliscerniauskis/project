<template>
    <div>
        <h1>Validate voucher</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else>
            <p>
                <RouterLink :to="`/providers/${providerId}`">Back to provider</RouterLink>
            </p>

            <form @submit.prevent="onSubmit">
                <label for="code">Voucher code</label>
                <input
                    id="code"
                    v-model="code"
                    type="text"
                    autocomplete="off"
                />

                <button type="submit" :disabled="submitting">
                    {{ submitting ? 'Checking...' : 'Check voucher' }}
                </button>
            </form>

            <div v-if="result" style="margin-top: 1rem;">
                <p><strong>Valid:</strong> {{ result.valid ? 'Yes' : 'No' }}</p>
                <p><strong>Status:</strong> {{ result.status }}</p>
                <p><strong>Reason:</strong> {{ result.reason ?? '-' }}</p>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { validateVoucher, type VoucherValidationView } from '@/api/voucher.api'

const route = useRoute()

const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const providerId = ref('')
const code = ref('')
const result = ref<VoucherValidationView | null>(null)

onMounted(() => {
    const id = route.params.id

    if (typeof id !== 'string' || id.length === 0) {
        error.value = 'Invalid provider id.'
        loading.value = false
        return
    }

    providerId.value = id
    loading.value = false
})

async function onSubmit() {
    if (providerId.value.length === 0 || code.value.trim().length === 0) {
        return
    }

    submitting.value = true
    error.value = ''
    result.value = null

    try {
        const response = await validateVoucher(providerId.value, {
            code: code.value.trim(),
        })

        result.value = response.data
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to validate voucher.'
    } finally {
        submitting.value = false
    }
}
</script>
