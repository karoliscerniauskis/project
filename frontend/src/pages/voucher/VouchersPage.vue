<template>
    <div class="min-h-screen bg-primary-50">
        <TopNav />
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold text-primary-900">Vouchers</h1>
                <p class="text-primary-600 mt-1">View and manage your claimed vouchers</p>
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
                <div v-if="!vouchers || vouchers.length === 0" class="text-center py-8">
                    <p class="text-primary-600">No vouchers found</p>
                </div>

                <el-table v-else :data="vouchers" style="width: 100%">
                    <el-table-column prop="code" label="Code" width="250" />
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
                    <el-table-column label="Actions" width="280" align="right">
                        <template #default="{ row }">
                            <el-button
                                v-if="row.canBeClaimedOrTransferred"
                                type="success"
                                size="small"
                                @click="handleClaim(row)"
                            >
                                Claim
                            </el-button>
                            <el-button
                                v-if="row.canBeClaimedOrTransferred"
                                type="primary"
                                size="small"
                                @click="handleTransfer(row)"
                            >
                                Transfer
                            </el-button>
                            <el-button
                                v-if="row.status === 'active'"
                                type="warning"
                                size="small"
                                @click="handleChangeProvider(row)"
                            >
                                Change Provider
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
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

        <el-dialog v-model="transferDialogVisible" title="Transfer Voucher" width="500">
            <el-form :model="transferForm" :rules="transferRules" ref="transferFormRef" label-position="top">
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
import { Loading } from '@element-plus/icons-vue'
import { voucherApi, type Voucher } from '@/api/voucher.api'
import TopNav from '@/components/layout/TopNav.vue'
import ChangeVoucherProviderForm from '@/components/voucher/ChangeVoucherProviderForm.vue'
import { formatRelativeDate } from '@/utils/date'
import { getVoucherStatusType } from '@/utils/status'
import { useAsyncState } from '@/composables/useAsyncState'
import { validationRules } from '@/utils/validationRules'
import { MESSAGES } from '@/constants/messages'
import { formatCents } from '@/utils/currency'

const { data: vouchers, loading, error, execute: fetchVouchers } = useAsyncState<Voucher[]>()

const claimDialogVisible = ref(false)
const claimLoading = ref(false)
const transferDialogVisible = ref(false)
const transferLoading = ref(false)
const transferFormRef = ref<FormInstance>()
const changeProviderDialogVisible = ref(false)
const selectedVoucher = ref<Voucher | null>(null)
const transferForm = ref({
    toEmail: '',
})

const transferRules = {
    toEmail: validationRules.email(),
}

async function loadVouchers() {
    await fetchVouchers(() => voucherApi.getUserVouchers(), {
        errorMessage: MESSAGES.ERROR.VOUCHER_LOAD,
    })
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
    } catch (err: any) {
        ElMessage.error(err.response?.data?.message || MESSAGES.ERROR.VOUCHER_CLAIM)
    } finally {
        claimLoading.value = false
    }
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
        } catch (err: any) {
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

onMounted(() => {
    loadVouchers()
})
</script>
