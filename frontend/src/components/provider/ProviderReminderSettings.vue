<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="mb-4">
            <h2 class="text-xl font-semibold text-primary-900 mb-2">Reminder Settings</h2>
            <p class="text-sm text-primary-600">
                Configure when to send reminder notifications for vouchers
            </p>
        </div>

        <BaseForm
            v-model="form"
            :rules="rules"
            :loading="loading"
            :error="error"
            :success="success"
            submit-text="Save Settings"
            @submit="handleSubmit"
        >
            <el-form-item label="Claim Reminder (days after voucher created)" prop="claimReminderAfterDays">
                <el-input-number
                    v-model="form.claimReminderAfterDays"
                    :min="1"
                    placeholder="Days after creation"
                    size="large"
                    controls-position="right"
                    class="w-full"
                />
                <span class="text-xs text-gray-500 mt-1 block">
                    Send reminder to claim voucher X days after it was created (leave empty to disable)
                </span>
            </el-form-item>

            <el-form-item label="Expiry Reminder (days before expiration)" prop="expiryReminderBeforeDays">
                <el-input-number
                    v-model="form.expiryReminderBeforeDays"
                    :min="1"
                    placeholder="Days before expiry"
                    size="large"
                    controls-position="right"
                    class="w-full"
                />
                <span class="text-xs text-gray-500 mt-1 block">
                    Send reminder X days before voucher expires (leave empty to disable)
                </span>
            </el-form-item>
        </BaseForm>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { providerApi } from '@/api/provider.api'
import BaseForm from '@/components/base/BaseForm.vue'
import { useAsyncAction } from '@/composables/useAsyncState'
import { validationRules } from '@/utils/validationRules'
import { MESSAGES } from '@/constants/messages'

const props = defineProps<{
    providerId: string
}>()

interface FormData {
    claimReminderAfterDays: number | null
    expiryReminderBeforeDays: number | null
}

const form = ref<FormData>({
    claimReminderAfterDays: null,
    expiryReminderBeforeDays: null,
})

const rules = {
    claimReminderAfterDays: validationRules.positive('Must be at least 1 day'),
    expiryReminderBeforeDays: validationRules.positive('Must be at least 1 day'),
}

const { loading, error, success, execute } = useAsyncAction()

async function handleSubmit() {
    await execute(
        async () => {
            await providerApi.configureReminderSettings(props.providerId, {
                claimReminderAfterDays: form.value.claimReminderAfterDays,
                expiryReminderBeforeDays: form.value.expiryReminderBeforeDays,
            })
        },
        {
            successMessage: MESSAGES.SUCCESS.REMINDER_SETTINGS_SAVED,
            errorMessage: MESSAGES.ERROR.REMINDER_SETTINGS_SAVE,
        }
    )
}
</script>
