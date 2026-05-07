<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Provider Vouchers</h1>
                        <p class="text-slate-600 mt-1">
                            Manage and monitor vouchers for this provider
                        </p>
                    </div>
                    <RouterLink
                        :to="`/providers/${providerId}`"
                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors"
                    >
                        ← Back to Provider
                    </RouterLink>
                </div>
            </div>

            <LoadingSpinner v-if="loading" message="Loading vouchers..." />

            <ErrorMessage v-else-if="error" :message="error" />

            <EmptyState
                v-else-if="vouchers.length === 0"
                title="No vouchers found"
                description="This provider hasn't created any vouchers yet."
            />

            <DataTable v-else :columns="columns" :data="vouchers" row-key="id">
                <template #cell-code="{ value }">
                    <span class="font-mono text-sm text-slate-900">
                        {{ value }}
                    </span>
                </template>

                <template #cell-type="{ value }">
                    <span class="capitalize text-sm text-slate-700">
                        {{ value }}
                    </span>
                </template>

                <template #cell-balance="{ row }">
                    <span class="text-sm text-slate-700">
                        {{ formatVoucherBalance(row) }}
                    </span>
                </template>

                <template #cell-claimedByUser="{ value }">
                    <span class="text-sm text-slate-600">
                        {{ value ?? '-' }}
                    </span>
                </template>

                <template #cell-status="{ value }">
                    <StatusBadge :status="value as VoucherStatus" />
                </template>

                <template #cell-actions="{ row }">
                    <BaseButton
                        v-if="row.status === 'active'"
                        variant="danger"
                        size="sm"
                        :disabled="deactivatingVoucherId === row.id"
                        :loading="deactivatingVoucherId === row.id"
                        @click="deactivateVoucher(row.id)"
                    >
                        Deactivate
                    </BaseButton>
                    <span v-else class="text-slate-400">-</span>
                </template>
            </DataTable>
        </div>
    </main>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { deactivateProviderVoucher, getProviderVouchers } from '@/api/provider.api'
import { useAsyncData } from '@/composables/useAsyncData'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorMessage from '@/components/common/ErrorMessage.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import DataTable from '@/components/common/DataTable.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import type { VoucherStatus } from '@/utils/status'
import type { ProviderVoucherView } from '@/api/provider.api'

const route = useRoute()
const providerId = ref('')
const deactivatingVoucherId = ref('')

onMounted(() => {
    const id = route.params.id
    if (typeof id !== 'string' || id.length === 0) {
        providerId.value = ''
    } else {
        providerId.value = id
    }
})

const {
    loading,
    error,
    data: vouchersResponse,
    refresh,
} = useAsyncData(
    () => {
        if (!providerId.value) {
            throw new Error('Invalid provider id.')
        }
        return getProviderVouchers(providerId.value)
    },
    { immediate: false }
)

onMounted(() => {
    if (providerId.value) {
        refresh()
    }
})

const vouchers = computed(() => vouchersResponse.value?.data ?? [])

const columns = [
    { key: 'code', label: 'Code' },
    { key: 'issuedToEmail', label: 'Issued To' },
    { key: 'type', label: 'Type' },
    { key: 'balance', label: 'Balance' },
    { key: 'claimedByUser', label: 'Claimed By' },
    { key: 'createdByUser', label: 'Created By' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Actions' },
]

function formatMoney(amount: number | null): string {
    if (amount === null) {
        return '-'
    }

    return `${(amount / 100).toFixed(2)} €`
}

function formatVoucherBalance(voucher: ProviderVoucherView): string {
    if (voucher.type === 'amount') {
        return `${formatMoney(voucher.remainingAmount)} / ${formatMoney(voucher.initialAmount)}`
    }

    return `${voucher.remainingUsages ?? '-'} / ${voucher.initialUsages ?? '-'} usages`
}

async function deactivateVoucher(voucherId: string): Promise<void> {
    if (!providerId.value) {
        return
    }

    deactivatingVoucherId.value = voucherId

    try {
        await deactivateProviderVoucher(providerId.value, voucherId)
        await refresh()
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to deactivate voucher.'
    } finally {
        deactivatingVoucherId.value = ''
    }
}
</script>
