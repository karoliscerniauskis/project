<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <h2 class="text-xl font-semibold text-primary-900 mb-4">Change Password</h2>
        <BaseForm
            v-model="form"
            :rules="rules"
            :loading="loading"
            :error="error"
            :success="success"
            submit-text="Change Password"
            @submit="handleSubmit"
        >
            <el-form-item label="Current Password" prop="currentPassword" required>
                <el-input
                    v-model="form.currentPassword"
                    type="password"
                    placeholder="Enter current password"
                    size="large"
                    show-password
                />
            </el-form-item>
            <el-form-item label="New Password" prop="newPassword" required>
                <el-input
                    v-model="form.newPassword"
                    type="password"
                    placeholder="Enter new password"
                    size="large"
                    show-password
                />
            </el-form-item>
        </BaseForm>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { userApi } from '@/api/user.api'
import BaseForm from '@/components/base/BaseForm.vue'
import { useAsyncAction } from '@/composables/useAsyncState'
import { validationRules } from '@/utils/validationRules'
import { MESSAGES } from '@/constants/messages'

interface FormData {
    currentPassword: string
    newPassword: string
}

const form = ref<FormData>({
    currentPassword: '',
    newPassword: '',
})

const rules = {
    currentPassword: validationRules.password(),
    newPassword: validationRules.password(),
}

const { loading, error, success, execute } = useAsyncAction()

async function handleSubmit() {
    await execute(
        async () => {
            await userApi.changePassword(form.value)
            form.value.currentPassword = ''
            form.value.newPassword = ''
        },
        {
            successMessage: MESSAGES.SUCCESS.PASSWORD_CHANGED,
            errorMessage: MESSAGES.ERROR.PASSWORD_CHANGE,
        }
    )
}
</script>
