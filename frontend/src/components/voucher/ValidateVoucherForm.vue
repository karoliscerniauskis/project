<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-primary-900">Validate Voucher</h2>
        </div>

        <BaseForm
            v-model="form"
            :rules="rules"
            :loading="loading"
            :error="error"
            :success="success"
            submit-text="Validate"
            @submit="handleSubmit"
        >
            <el-form-item label="Voucher Code" prop="code" required>
                <el-input
                    v-model="form.code"
                    placeholder="Enter voucher code"
                    size="large"
                />
            </el-form-item>

            <el-form-item v-if="validationResult">
                <el-alert :type="validationResult.valid ? 'success' : 'error'" :closable="false" show-icon>
                    <template #title>
                        {{ validationResult.valid ? 'Valid Voucher' : 'Invalid Voucher' }}
                    </template>
                    <div>
                        <p><strong>Status:</strong> <el-tag :type="getStatusType(validationResult.status)" size="small">{{ getStatusMessage(validationResult.status) }}</el-tag></p>
                        <p v-if="validationResult.reason"><strong>Reason:</strong> {{ getReasonMessage(validationResult.reason) }}</p>
                    </div>
                </el-alert>
            </el-form-item>
        </BaseForm>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { voucherApi, type VoucherValidationResponse } from '@/api/voucher.api'
import BaseForm from '@/components/base/BaseForm.vue'
import { validationRules } from '@/utils/validationRules'
import { MESSAGES } from '@/constants/messages'

const props = defineProps<{
    providerId: string
}>()

interface FormData {
    code: string
}

const form = ref<FormData>({
    code: '',
})

const rules = {
    code: validationRules.required('Voucher code is required'),
}

const loading = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)
const validationResult = ref<VoucherValidationResponse | null>(null)

const getStatusType = (status: string): 'success' | 'info' | 'warning' | 'danger' => {
    switch (status) {
    case 'valid':
        return 'success'
    case 'notFound':
    case 'used':
        return 'danger'
    default:
        return 'info'
    }
}

const getStatusMessage = (status: string): string => {
    const statusMap: Record<string, string> = {
        valid: 'Valid',
        notFound: 'Not Found',
        used: 'Used',
    }
    return statusMap[status] || status
}

const getReasonMessage = (reason: string): string => {
    return MESSAGES.VALIDATION_REASON[reason as keyof typeof MESSAGES.VALIDATION_REASON] || reason
}

async function handleSubmit() {
    loading.value = true
    error.value = null
    success.value = null
    validationResult.value = null

    try {
        const result = await voucherApi.validateVoucher(props.providerId, {
            code: form.value.code,
        })
        validationResult.value = result

        if (result.valid) {
            success.value = MESSAGES.SUCCESS.VOUCHER_VALIDATED
        } else {
            error.value = MESSAGES.INFO.VOUCHER_INVALID
        }
    } catch (err) {
        error.value = err instanceof Error ? err.message : MESSAGES.ERROR.VOUCHER_VALIDATE
    } finally {
        loading.value = false
    }
}
</script>
