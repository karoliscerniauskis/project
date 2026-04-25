<template>
    <div>
        <h1>Transfer voucher</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else>
            <p v-if="transferred">Voucher transferred successfully.</p>

            <form v-else @submit.prevent="onTransfer">
                <label>
                    Recipient email
                    <input
                        v-model="recipientEmail"
                        type="email"
                        required
                        placeholder="recipient@example.com"
                    >
                </label>

                <button type="submit" :disabled="submitting">
                    {{ submitting ? 'Transferring...' : 'Transfer voucher' }}
                </button>
            </form>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { transferVoucher } from '@/api/voucher.api'

const route = useRoute()
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const voucherId = ref('')
const recipientEmail = ref('')
const transferred = ref(false)

onMounted(() => {
    const id = route.params.voucherId

    if (typeof id !== 'string' || id.length === 0) {
        error.value = 'Invalid voucher id.'
        loading.value = false
        return
    }

    voucherId.value = id
    loading.value = false
})

async function onTransfer(): Promise<void> {
    if (!voucherId.value || !recipientEmail.value.trim()) {
        return
    }

    submitting.value = true
    error.value = ''

    try {
        await transferVoucher(voucherId.value, {
            recipientEmail: recipientEmail.value.trim(),
        })
        transferred.value = true
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to transfer voucher.'
    } finally {
        submitting.value = false
    }
}
</script>
