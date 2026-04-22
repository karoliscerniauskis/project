<template>
    <div>
        <h1>Claim voucher</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else>
            <p v-if="claimed">Voucher claimed successfully.</p>
            <button v-else type="button" :disabled="submitting" @click="onClaim">
                {{ submitting ? 'Claiming...' : 'Claim voucher' }}
            </button>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { claimVoucher } from '@/api/voucher.api'

const route = useRoute()
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const voucherId = ref('')
const claimed = ref(false)

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

async function onClaim(): Promise<void> {
    if (!voucherId.value) {
        return
    }

    submitting.value = true
    error.value = ''

    try {
        await claimVoucher(voucherId.value)
        claimed.value = true
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to claim voucher.'
    } finally {
        submitting.value = false
    }
}
</script>
