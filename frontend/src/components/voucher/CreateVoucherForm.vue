<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-primary-900">Create Voucher</h2>
        </div>

        <BaseForm
            v-model="form"
            :rules="rules"
            :loading="loading"
            :error="error"
            :success="success"
            submit-text="Create Voucher"
            @submit="handleSubmit"
        >
            <el-form-item label="Recipient Email" prop="issuedToEmail" required>
                <el-input
                    v-model="form.issuedToEmail"
                    type="email"
                    placeholder="Enter recipient email"
                    size="large"
                />
            </el-form-item>

            <el-form-item label="Voucher Type" prop="type" required>
                <el-radio-group v-model="form.type" size="large">
                    <el-radio value="amount">Amount Based</el-radio>
                    <el-radio value="usage">Usage Based</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item
                v-if="form.type === 'amount'"
                label="Amount (€)"
                prop="amount"
                required
            >
                <el-input-number
                    v-model="form.amount"
                    :min="0.01"
                    :step="1"
                    :precision="2"
                    placeholder="Enter amount in euros"
                    size="large"
                    controls-position="right"
                    class="w-full"
                />
            </el-form-item>

            <el-form-item
                v-if="form.type === 'usage'"
                label="Number of Usages"
                prop="usages"
                required
            >
                <el-input-number
                    v-model="form.usages"
                    :min="1"
                    :step="1"
                    placeholder="Enter number of usages"
                    size="large"
                    controls-position="right"
                    class="w-full"
                />
            </el-form-item>

            <el-form-item label="Expires At" prop="expiresAt" :error="getFieldError('expiresAt')">
                <el-date-picker
                    v-model="form.expiresAt"
                    type="datetime"
                    placeholder="Select expiration date (optional)"
                    size="large"
                    style="width: 100%"
                    :disabled-date="(date: Date) => date < new Date()"
                />
            </el-form-item>

            <el-form-item label="Scheduled Send At" prop="scheduledSendAt" :error="getFieldError('scheduledSendAt')">
                <el-date-picker
                    v-model="form.scheduledSendAt"
                    type="datetime"
                    placeholder="Select scheduled send date (optional)"
                    size="large"
                    style="width: 100%"
                    :disabled-date="(date: Date) => date < new Date()"
                />
            </el-form-item>
        </BaseForm>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { voucherApi, type CreateVoucherRequest } from '@/api/voucher.api'
import BaseForm from '@/components/base/BaseForm.vue'
import { useAsyncAction } from '@/composables/useAsyncState'
import { validationRules } from '@/utils/validationRules'
import { MESSAGES } from '@/constants/messages'
import { eurosToCents } from '@/utils/currency'

const props = defineProps<{
    providerId: string
}>()

const emit = defineEmits<{
    created: [voucherId: string]
}>()

interface FormData {
    issuedToEmail: string
    type: 'amount' | 'usage'
    amount: number | null
    usages: number | null
    expiresAt: Date | null
    scheduledSendAt: Date | null
}

const form = ref<FormData>({
    issuedToEmail: '',
    type: 'amount',
    amount: null,
    usages: null,
    expiresAt: null,
    scheduledSendAt: null,
})

const rules = {
    issuedToEmail: validationRules.email(),
    type: validationRules.required('Voucher type is required'),
    amount: validationRules.conditionalRequired(
        () => form.value.type === 'amount',
        'Amount is required for amount-based vouchers'
    ),
    usages: validationRules.conditionalRequired(
        () => form.value.type === 'usage',
        'Usages is required for usage-based vouchers'
    ),
}

const { loading, error, success, fieldErrors, execute } = useAsyncAction()

function getFieldError(field: string): string {
    return fieldErrors.value.find(e => e.field === field)?.message || ''
}

watch(() => form.value.type, (newType) => {
    if (newType === 'amount') {
        form.value.usages = null
    } else {
        form.value.amount = null
    }
})

async function handleSubmit() {
    await execute(
        async () => {
            const request: CreateVoucherRequest = {
                issuedToEmail: form.value.issuedToEmail,
                type: form.value.type,
                amount: form.value.type === 'amount' && form.value.amount ? eurosToCents(form.value.amount) : null,
                usages: form.value.type === 'usage' ? form.value.usages : null,
                expiresAt: form.value.expiresAt?.toISOString() || null,
                scheduledSendAt: form.value.scheduledSendAt?.toISOString() || null,
            }

            const response = await voucherApi.createVoucher(props.providerId, request)

            form.value = {
                issuedToEmail: '',
                type: 'amount',
                amount: null,
                usages: null,
                expiresAt: null,
                scheduledSendAt: null,
            }

            emit('created', response.id)
        },
        {
            successMessage: MESSAGES.SUCCESS.VOUCHER_CREATED,
            errorMessage: MESSAGES.ERROR.VOUCHER_CREATE,
        }
    )
}
</script>
