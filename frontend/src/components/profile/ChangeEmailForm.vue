<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <h2 class="text-xl font-semibold text-primary-900 mb-4">Change Email</h2>
        <BaseForm
            v-model="form"
            :rules="rules"
            :loading="loading"
            :error="error"
            :success="success"
            submit-text="Change Email"
            @submit="handleSubmit"
        >
            <el-form-item label="New Email" prop="newEmail" required>
                <el-input
                    v-model="form.newEmail"
                    type="email"
                    placeholder="Enter new email address"
                    size="large"
                />
            </el-form-item>
        </BaseForm>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { userApi } from '@/api/user.api'
import { useAuth } from '@/composables/useAuth'
import BaseForm from '@/components/base/BaseForm.vue'
import { useAsyncAction } from '@/composables/useAsyncState'
import { validationRules } from '@/utils/validationRules'
import { MESSAGES } from '@/constants/messages'

const { logout } = useAuth()

interface FormData {
    newEmail: string
}

const form = ref<FormData>({
    newEmail: '',
})

const rules = {
    newEmail: validationRules.email(),
}

const { loading, error, success, execute } = useAsyncAction()

async function handleSubmit() {
    await execute(
        async () => {
            await userApi.changeEmail(form.value)
            form.value.newEmail = ''

            setTimeout(() => {
                logout()
            }, 2000)
        },
        {
            successMessage: MESSAGES.SUCCESS.EMAIL_CHANGED,
            errorMessage: MESSAGES.ERROR.EMAIL_CHANGE,
        }
    )
}
</script>
