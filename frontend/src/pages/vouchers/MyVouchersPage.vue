<template>
    <div>
        <h1>My vouchers</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else>
            <p v-if="vouchers.length === 0">No vouchers found.</p>

            <table v-else>
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Provider</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="voucher in vouchers" :key="voucher.code">
                    <td>{{ voucher.code }}</td>
                    <td>{{ voucher.providerName }}</td>
                </tr>
                </tbody>
            </table>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { getMyVouchers, type MyVoucherView } from '@/api/voucher.api'

const loading = ref(true)
const error = ref('')
const vouchers = ref<MyVoucherView[]>([])

onMounted(async () => {
    try {
        const response = await getMyVouchers()
        vouchers.value = response.data
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load vouchers.'
    } finally {
        loading.value = false
    }
})
</script>
