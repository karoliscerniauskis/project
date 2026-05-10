<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-primary-900">Use Voucher</h2>
        </div>

        <BaseForm
            v-model="form"
            :rules="rules"
            :loading="loading"
            :error="error"
            :success="success"
            submit-text="Use Voucher"
            @submit="handleSubmit"
        >
            <el-form-item label="Voucher Code" prop="code" required>
                <el-input
                    v-model="form.code"
                    placeholder="Enter voucher code"
                    size="large"
                />
            </el-form-item>

            <el-form-item label="Amount (€, optional)" prop="amount">
                <el-input-number
                    v-model="form.amount"
                    :min="0.01"
                    :step="0.01"
                    :precision="2"
                    placeholder="Enter amount to use in euros"
                    size="large"
                    controls-position="right"
                    style="width: 100%"
                />
            </el-form-item>
        </BaseForm>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { voucherApi } from '@/api/voucher.api'
import BaseForm from '@/components/base/BaseForm.vue'
import { useAsyncAction } from '@/composables/useAsyncState'
import { validationRules } from '@/utils/validationRules'
import { MESSAGES } from '@/constants/messages'
import { eurosToCents } from '@/utils/currency'

const props = defineProps<{
    providerId: string
}>()

const emit = defineEmits<{
    used: []
}>()

interface FormData {
    code: string
    amount: number | null
}

const form = ref<FormData>({
    code: '',
    amount: null,
})

const rules = {
    code: validationRules.required('Voucher code is required'),
}

const { loading, error, success, execute } = useAsyncAction()

async function handleSubmit() {
    await execute(
        async () => {
            await voucherApi.useVoucher(props.providerId, {
                code: form.value.code,
                amount: form.value.amount ? eurosToCents(form.value.amount) : null,
            })
            form.value.code = ''
            form.value.amount = null
            emit('used')
        },
        {
            successMessage: MESSAGES.SUCCESS.VOUCHER_USED,
            errorMessage: MESSAGES.ERROR.VOUCHER_USE,
        }
    )
}
</script>
