<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-center justify-between mb-4">
            <div class="flex-1">
                <h2 class="text-xl font-semibold text-primary-900">Vouchers</h2>
                <p class="text-sm text-gray-500 mt-1">Click the expand icon to view usage history</p>
            </div>
            <div class="flex items-center gap-3">
                <el-input
                    v-model="searchCode"
                    placeholder="Search by code"
                    clearable
                    style="width: 200px"
                    @input="handleSearch"
                >
                    <template #prefix>
                        <el-icon><Search /></el-icon>
                    </template>
                </el-input>
                <el-button :loading="loading" size="small" @click="loadVouchers">
                    <el-icon><Refresh /></el-icon>
                </el-button>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-8">
            <el-icon class="is-loading" :size="24">
                <Loading />
            </el-icon>
        </div>

        <div v-else-if="error" class="text-center py-8">
            <p class="text-red-600">{{ error }}</p>
        </div>

        <div v-else-if="!vouchers || vouchers.length === 0" class="text-center py-8">
            <p class="text-gray-500">No vouchers found</p>
        </div>

        <el-table
            v-else
            :data="vouchers"
            style="width: 100%"
            @expand-change="handleExpandChange"
        >
            <el-table-column type="expand">
                <template #default="{ row }">
                    <div class="py-4 bg-gray-50">
                        <div class="max-w-full mx-auto px-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">Usage History</h3>
                            <div v-if="loadingUsages[row.id]" class="text-center py-8">
                                <el-icon class="is-loading" :size="24"><Loading /></el-icon>
                                <p class="text-gray-500 mt-2">Loading usage history...</p>
                            </div>
                            <div v-else-if="voucherUsages[row.id] && voucherUsages[row.id].length > 0">
                                <el-table :data="voucherUsages[row.id]" style="width: 100%" :show-header="true">
                                    <el-table-column label="Used Amount" min-width="200">
                                        <template #default="{ row: usage }">
                                            <span class="font-medium">
                                                {{ usage.usedAmount ? formatCents(usage.usedAmount) : 'Usage-based (1 use)' }}
                                            </span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Used At" min-width="200">
                                        <template #default="{ row: usage }">
                                            {{ formatRelativeDate(usage.usedAt) }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="Full Date" min-width="250">
                                        <template #default="{ row: usage }">
                                            {{ new Date(usage.usedAt).toLocaleDateString('lt-LT', { year: 'numeric', month: '2-digit', day: '2-digit' }) }}
                                            {{ new Date(usage.usedAt).toLocaleTimeString('lt-LT', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }}
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                            <div v-else class="text-center py-8">
                                <el-icon :size="32" class="text-gray-400 mb-2">
                                    <InfoFilled />
                                </el-icon>
                                <p class="text-gray-500">No usage history yet</p>
                            </div>
                        </div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="code" label="Code" width="200" />
            <el-table-column prop="issuedToEmail" label="Issued To" min-width="180" />
            <el-table-column label="Claimed By" min-width="180">
                <template #default="{ row }">
                    {{ row.claimedByUser || '-' }}
                </template>
            </el-table-column>
            <el-table-column label="Status" width="120">
                <template #default="{ row }">
                    <el-tag :type="getVoucherStatusType(row.status)" size="small">
                        {{ row.status }}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column label="Type/Value" width="180">
                <template #default="{ row }">
                    <span v-if="row.type === 'amount'">
                        {{ formatCents(row.remainingAmount) }}/{{ formatCents(row.initialAmount) }}
                    </span>
                    <span v-else>
                        Usages: {{ row.remainingUsages }}/{{ row.initialUsages }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column label="Expires" width="150">
                <template #default="{ row }">
                    {{ row.expiresAt ? formatRelativeDate(row.expiresAt) : 'Never' }}
                </template>
            </el-table-column>
            <el-table-column label="Actions" width="230" align="right">
                <template #default="{ row }">
                    <el-button
                        v-if="row.code"
                        type="success"
                        size="small"
                        :loading="generatePhysicalLoading && selectedVoucher?.id === row.id"
                        @click.stop="handleGeneratePhysical(row)"
                    >
                        Physical
                    </el-button>
                    <el-button
                        v-if="row.status === 'active'"
                        type="danger"
                        size="small"
                        @click="handleDeactivate(row)"
                    >
                        Deactivate
                    </el-button>
                </template>
            </el-table-column>
        </el-table>

        <div v-if="vouchers && vouchers.length > 0" class="mt-6 flex justify-center">
            <el-pagination
                v-model:current-page="currentPage"
                :page-size="perPage"
                :total="total"
                layout="prev, pager, next, total"
                @current-change="handlePageChange"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { Loading, Refresh, InfoFilled, Search } from '@element-plus/icons-vue'
import { voucherApi, type ProviderVoucher, type VoucherUsage, type PaginatedProviderVouchers } from '@/api/voucher.api'
import { getVoucherStatusType } from '@/utils/status'
import { useAsyncState } from '@/composables/useAsyncState'
import { useConfirm } from '@/composables/useConfirm'
import { MESSAGES } from '@/constants/messages'
import { formatCents } from '@/utils/currency'
import { formatRelativeDate } from '@/utils/date'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps<{
    providerId: string
}>()

const { data: paginatedData, loading, error, execute: fetchVouchers } = useAsyncState<PaginatedProviderVouchers>()
const { confirm } = useConfirm()
const voucherUsages = ref<Record<string, VoucherUsage[]>>({})
const loadingUsages = ref<Record<string, boolean>>({})
const searchCode = ref('')
const currentPage = ref(1)
const perPage = ref(20)
const generatePhysicalLoading = ref(false)
const selectedVoucher = ref<ProviderVoucher | null>(null)

const vouchers = ref<ProviderVoucher[]>([])
const total = ref(0)

async function loadVouchers() {
    await fetchVouchers(() => voucherApi.getProviderVouchers(
        props.providerId,
        searchCode.value || undefined,
        currentPage.value,
        perPage.value
    ), {
        errorMessage: MESSAGES.ERROR.VOUCHER_LOAD,
    })

    if (paginatedData.value) {
        vouchers.value = paginatedData.value.data
        total.value = paginatedData.value.meta.total
    }
}

const handleSearch = useDebounceFn(() => {
    currentPage.value = 1
    loadVouchers()
}, 300)

function handlePageChange(page: number) {
    currentPage.value = page
    loadVouchers()
}

async function handleExpandChange(row: ProviderVoucher, expandedRows: ProviderVoucher[]) {
    const isExpanding = expandedRows.some(r => r.id === row.id)

    if (!isExpanding) {
        return
    }

    if (voucherUsages.value[row.id]) {
        return
    }

    loadingUsages.value[row.id] = true

    try {
        const usages = await voucherApi.getVoucherUsages(row.id)
        voucherUsages.value[row.id] = usages
    } catch {
        ElMessage.error('Failed to load usage history')
    } finally {
        loadingUsages.value[row.id] = false
    }
}

async function handleDeactivate(voucher: ProviderVoucher) {
    const confirmed = await confirm(
        MESSAGES.CONFIRM.VOUCHER_DEACTIVATE(voucher.code),
        'Confirm Deactivation'
    )

    if (!confirmed) return

    try {
        await voucherApi.deactivateVoucher(props.providerId, voucher.id)
        ElMessage.success(MESSAGES.SUCCESS.VOUCHER_DEACTIVATED)
        await loadVouchers()
    } catch (err: unknown) {
        ElMessage.error(err.response?.data?.message || MESSAGES.ERROR.VOUCHER_DEACTIVATE)
    }
}

async function handleGeneratePhysical(voucher: ProviderVoucher) {
    selectedVoucher.value = voucher
    generatePhysicalLoading.value = true

    try {
        const response = await voucherApi.generatePhysicalVoucher(voucher.id)
        const link = document.createElement('a')
        link.href = response.imageUrl
        link.download = `voucher-${voucher.code}.png`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)

        ElMessage.success('Physical voucher downloaded successfully!')
    } catch (err: unknown) {
        ElMessage.error(err.response?.data?.message || 'Failed to generate physical voucher')
    } finally {
        generatePhysicalLoading.value = false
        selectedVoucher.value = null
    }
}

onMounted(() => {
    loadVouchers()
})

defineExpose({
    fetchVouchers: loadVouchers,
})
</script>
