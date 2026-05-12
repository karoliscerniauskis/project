<template>
    <div class="min-h-screen bg-primary-50">
        <TopNav />
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold text-primary-900">Vouchers</h1>
                <p class="text-primary-600 mt-1">View and manage your claimed vouchers. Click the expand icon to view usage history.</p>
            </div>

            <div v-if="loading" class="flex justify-center items-center py-12">
                <el-icon class="is-loading" :size="32">
                    <Loading />
                </el-icon>
            </div>

            <div v-else-if="error" class="text-center py-12">
                <p class="text-red-600">{{ error }}</p>
            </div>

            <div v-else class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold text-primary-900">My Vouchers</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <el-button type="primary" @click="importDialogVisible = true">
                            Import Voucher
                        </el-button>
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

                <div v-if="!vouchers || vouchers.length === 0" class="text-center py-8">
                    <p class="text-primary-600">No vouchers found</p>
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
                    <el-table-column label="Code" width="250">
                        <template #default="{ row }">
                            <span v-if="row.isCodeVisible">{{ row.code }}</span>
                            <el-button v-if="row.canBeClaimed" type="primary" size="small" @click.stop="handleClaim(row)">
                                Claim to reveal
                            </el-button>
                        </template>
                    </el-table-column>
                    <el-table-column prop="providerName" label="Provider" min-width="150" />
                    <el-table-column label="Status" width="120">
                        <template #default="{ row }">
                            <el-tag :type="getVoucherStatusType(row.status)" size="small">
                                {{ row.status }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="Type/Value" width="200">
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
                    <el-table-column label="Actions" width="290" align="right">
                        <template #default="{ row }">
                            <el-button
                                v-if="row.canBeTransferred"
                                type="success"
                                size="small"
                                :loading="generatePhysicalLoading && selectedVoucher?.id === row.id"
                                @click.stop="handleGeneratePhysical(row)"
                            >
                                Physical
                            </el-button>
                            <el-button
                                v-if="row.canBeTransferred"
                                type="primary"
                                size="small"
                                @click.stop="handleTransfer(row)"
                            >
                                Transfer
                            </el-button>
                            <el-button
                                v-if="row.canProviderBeChanged"
                                size="small"
                                @click.stop="handleChangeProvider(row)"
                            >
                                Change
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
        </div>

        <el-dialog v-model="claimDialogVisible" title="Claim Voucher" width="500">
            <template #footer>
                <el-button @click="claimDialogVisible = false">Cancel</el-button>
                <el-button type="success" :loading="claimLoading" @click="confirmClaim">
                    Claim
                </el-button>
            </template>
        </el-dialog>

        <el-dialog v-model="importDialogVisible" title="Import Voucher" width="500">
            <el-form ref="importFormRef" :model="importForm" :rules="importRules" label-position="top">
                <el-form-item label="Voucher Code" prop="code">
                    <el-input v-model="importForm.code" placeholder="Enter voucher code" />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="importDialogVisible = false">Cancel</el-button>
                <el-button type="primary" :loading="importLoading" @click="confirmImport">
                    Import
                </el-button>
            </template>
        </el-dialog>

        <el-dialog v-model="transferDialogVisible" title="Transfer Voucher" width="500">
            <el-alert
                type="warning"
                :closable="false"
                show-icon
                class="mb-4"
            >
                <template #title>
                    <span class="font-semibold">Important Notice</span>
                </template>
                After transferring this voucher, you will no longer have access to it. The recipient will become the new owner.
            </el-alert>
            <el-form ref="transferFormRef" :model="transferForm" :rules="transferRules" label-position="top">
                <el-form-item label="Transfer to Email" prop="toEmail">
                    <el-input v-model="transferForm.toEmail" type="email" placeholder="Enter recipient email" />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="transferDialogVisible = false">Cancel</el-button>
                <el-button type="primary" :loading="transferLoading" @click="confirmTransfer">
                    Transfer
                </el-button>
            </template>
        </el-dialog>
        <ChangeVoucherProviderForm
            v-if="selectedVoucher && changeProviderDialogVisible"
            v-model:visible="changeProviderDialogVisible"
            :voucher="selectedVoucher"
            @changed="handleProviderChanged"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage, type FormInstance } from 'element-plus'
import { Loading, InfoFilled, Search, Refresh } from '@element-plus/icons-vue'
import { voucherApi, type Voucher, type VoucherUsage, type PaginatedVouchers } from '@/api/voucher.api'
import TopNav from '@/components/layout/TopNav.vue'
import ChangeVoucherProviderForm from '@/components/voucher/ChangeVoucherProviderForm.vue'
import { formatRelativeDate } from '@/utils/date'
import { getVoucherStatusType } from '@/utils/status'
import { useAsyncState } from '@/composables/useAsyncState'
import { validationRules } from '@/utils/validationRules'
import { MESSAGES } from '@/constants/messages'
import { formatCents } from '@/utils/currency'
import { useDebounceFn } from '@vueuse/core'

const { data: paginatedData, loading, error, execute: fetchVouchers } = useAsyncState<PaginatedVouchers>()

const claimDialogVisible = ref(false)
const claimLoading = ref(false)
const importDialogVisible = ref(false)
const importLoading = ref(false)
const importFormRef = ref<FormInstance>()
const transferDialogVisible = ref(false)
const transferLoading = ref(false)
const transferFormRef = ref<FormInstance>()
const changeProviderDialogVisible = ref(false)
const generatePhysicalLoading = ref(false)
const selectedVoucher = ref<Voucher | null>(null)
const transferForm = ref({
    toEmail: '',
})
const importForm = ref({
    code: '',
})
const voucherUsages = ref<Record<string, VoucherUsage[]>>({})
const loadingUsages = ref<Record<string, boolean>>({})
const searchCode = ref('')
const currentPage = ref(1)
const perPage = ref(20)

const vouchers = ref<Voucher[]>([])
const total = ref(0)

const transferRules = {
    toEmail: validationRules.email(),
}

const importRules = {
    code: [{ required: true, message: 'Voucher code is required', trigger: 'blur' }],
}

async function loadVouchers() {
    await fetchVouchers(() => voucherApi.getUserVouchers(
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

async function handleExpandChange(row: Voucher, expandedRows: Voucher[]) {
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

function handleClaim(voucher: Voucher) {
    selectedVoucher.value = voucher
    claimDialogVisible.value = true
}

async function confirmClaim() {
    if (!selectedVoucher.value) return

    claimLoading.value = true

    try {
        await voucherApi.claimVoucher(selectedVoucher.value.id)
        ElMessage.success(MESSAGES.SUCCESS.VOUCHER_CLAIMED)
        claimDialogVisible.value = false
        await loadVouchers()
    } catch (err: unkown) {
        ElMessage.error(err.response?.data?.message || MESSAGES.ERROR.VOUCHER_CLAIM)
    } finally {
        claimLoading.value = false
    }
}

async function confirmImport() {
    if (!importFormRef.value) return

    await importFormRef.value.validate(async (valid) => {
        if (!valid) return

        importLoading.value = true

        try {
            await voucherApi.importVoucher({
                code: importForm.value.code,
            })
            ElMessage.success('Voucher imported successfully')
            importDialogVisible.value = false
            importForm.value.code = ''
            await loadVouchers()
        } catch (err: unknown) {
            ElMessage.error(err.message || 'Failed to import voucher')
        } finally {
            importLoading.value = false
        }
    })
}

function handleTransfer(voucher: Voucher) {
    selectedVoucher.value = voucher
    transferForm.value.toEmail = ''
    transferDialogVisible.value = true
}

async function confirmTransfer() {
    if (!transferFormRef.value || !selectedVoucher.value) return

    await transferFormRef.value.validate(async (valid) => {
        if (!valid) return

        transferLoading.value = true

        try {
            await voucherApi.transferVoucher(selectedVoucher.value!.id, {
                recipientEmail: transferForm.value.toEmail,
            })
            ElMessage.success(MESSAGES.SUCCESS.VOUCHER_TRANSFERRED)
            transferDialogVisible.value = false
            await loadVouchers()
        } catch (err: unknown) {
            ElMessage.error(err.response?.data?.message || MESSAGES.ERROR.VOUCHER_TRANSFER)
        } finally {
            transferLoading.value = false
        }
    })
}

function handleChangeProvider(voucher: Voucher) {
    selectedVoucher.value = voucher
    changeProviderDialogVisible.value = true
}

async function handleProviderChanged() {
    await loadVouchers()
}

async function handleGeneratePhysical(voucher: Voucher) {
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
    } catch (err: unkown) {
        ElMessage.error(err.response?.data?.message || 'Failed to generate physical voucher')
    } finally {
        generatePhysicalLoading.value = false
        selectedVoucher.value = null
    }
}

onMounted(() => {
    loadVouchers()
})
</script>
