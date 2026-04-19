<template>
    <div>
        <h1>Provider vouchers</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else>
            <p>
                <RouterLink :to="`/providers/${providerId}`">Back to provider</RouterLink>
            </p>

            <p v-if="vouchers.length === 0">No vouchers found.</p>

            <table v-else>
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Issued to</th>
                    <th>Claimed by user</th>
                    <th>Created by</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="voucher in vouchers" :key="voucher.code">
                    <td>{{ voucher.code }}</td>
                    <td>{{ voucher.issuedToEmail }}</td>
                    <td>{{ voucher.claimedByUser ?? '-' }}</td>
                    <td>{{ voucher.createdByUser }}</td>
                </tr>
                </tbody>
            </table>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { getProviderVouchers, type ProviderVoucherView } from '@/api/provider.api'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const providerId = ref('')
const vouchers = ref<ProviderVoucherView[]>([])

onMounted(async () => {
    const id = route.params.id

    if (typeof id !== 'string' || id.length === 0) {
        loading.value = false
        error.value = 'Invalid provider id.'
        return
    }

    providerId.value = id

    try {
        const response = await getProviderVouchers(id)
        vouchers.value = response.data
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load vouchers.'
    } finally {
        loading.value = false
    }
})
</script>
