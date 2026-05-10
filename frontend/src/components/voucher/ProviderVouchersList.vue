<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-primary-900">Vouchers</h2>
            <el-button @click="loadVouchers" :loading="loading" size="small">
                <el-icon><Refresh /></el-icon>
            </el-button>
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

        <el-table v-else :data="vouchers" style="width: 100%">
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
            <el-table-column label="Actions" width="120" align="right">
                <template #default="{ row }">
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
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Loading, Refresh } from '@element-plus/icons-vue'
import { voucherApi, type ProviderVoucher } from '@/api/voucher.api'
import { getVoucherStatusType } from '@/utils/status'
import { useAsyncState } from '@/composables/useAsyncState'
import { useConfirm } from '@/composables/useConfirm'
import { MESSAGES } from '@/constants/messages'
import { formatCents } from '@/utils/currency'
import { formatRelativeDate } from '@/utils/date'

const props = defineProps<{
    providerId: string
}>()

const { data: vouchers, loading, error, execute: fetchVouchers } = useAsyncState<ProviderVoucher[]>()
const { confirm } = useConfirm()

async function loadVouchers() {
    await fetchVouchers(() => voucherApi.getProviderVouchers(props.providerId), {
        errorMessage: MESSAGES.ERROR.VOUCHER_LOAD,
    })
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
    } catch (err: any) {
        ElMessage.error(err.response?.data?.message || MESSAGES.ERROR.VOUCHER_DEACTIVATE)
    }
}

onMounted(() => {
    loadVouchers()
})

defineExpose({
    fetchVouchers: loadVouchers,
})
</script>
