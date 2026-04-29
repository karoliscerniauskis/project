<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h1 class="text-3xl font-bold text-slate-900">My Vouchers</h1>
                <p class="text-slate-600 mt-1">View and manage your vouchers</p>
            </div>

            <LoadingSpinner v-if="loading" message="Loading vouchers..." />

            <ErrorMessage v-else-if="error" :message="error" />

            <EmptyState
                v-else-if="vouchers.length === 0"
                title="No vouchers found"
                description="You don't have any vouchers yet."
            />

            <DataTable v-else :columns="tableColumns" :data="vouchers">
                <template #cell-code="{ value }">
                    <span v-if="value" class="font-mono text-sm text-slate-900">
                        {{ value }}
                    </span>
                    <span v-else class="text-sm text-slate-500 italic">Claim to reveal</span>
                </template>

                <template #cell-providerName="{ value }">
                    <span class="text-sm text-slate-900">{{ value }}</span>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center gap-2">
                        <template v-if="row.code === null">
                            <BaseButton
                                variant="success"
                                size="sm"
                                :to="`/vouchers/${row.id}/claim`"
                            >
                                Claim
                            </BaseButton>
                            <BaseButton
                                variant="primary"
                                size="sm"
                                :to="`/vouchers/${row.id}/transfer`"
                            >
                                Transfer
                            </BaseButton>
                        </template>
                        <StatusBadge v-else status="claimed" />
                    </div>
                </template>
            </DataTable>
        </div>
    </main>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { getMyVouchers } from '@/api/voucher.api'
import { useAsyncData } from '@/composables/useAsyncData'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorMessage from '@/components/common/ErrorMessage.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import DataTable from '@/components/common/DataTable.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'

const tableColumns = [
    { key: 'code', label: 'Code' },
    { key: 'providerName', label: 'Provider' },
    { key: 'actions', label: 'Actions' },
]

const { loading, error, data: vouchersResponse } = useAsyncData(() => getMyVouchers())

const vouchers = computed(() => vouchersResponse.value?.data ?? [])
</script>
