<template>
    <BaseForm
        v-model="form"
        :rules="rules"
        :loading="loading"
        :error="error"
        :success="success"
        submit-text="Reset Password"
        loading-text="Resetting..."
        @submit="handleSubmit"
    >
        <el-form-item label="New Password" prop="password" required>
            <el-input
                v-model="form.password"
                type="password"
                placeholder="At least 8 characters"
                autocomplete="new-password"
                show-password
                size="large"
            />
        </el-form-item>

        <el-form-item label="Confirm New Password" prop="confirmPassword" required>
            <el-input
                v-model="form.confirmPassword"
                type="password"
                placeholder="••••••••"
                autocomplete="new-password"
                show-password
                size="large"
            />
        </el-form-item>
    </BaseForm>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import type { FormItemRule } from 'element-plus'
import type { ResetPasswordRequest } from '@/types'
import BaseForm from '@/components/base/BaseForm.vue'
import { validationRules } from '@/utils/validationRules'

const props = withDefaults(
    defineProps<{
        loading?: boolean
        error?: string | null
        success?: string | null
        resetToken: string
    }>(),
    {
        loading: false,
        error: null,
        success: null,
    }
)

const emit = defineEmits<{
    submit: [request: ResetPasswordRequest]
}>()

interface FormData {
    password: string
    confirmPassword: string
}

const form = reactive<FormData>({
    password: '',
    confirmPassword: '',
})

const validatePasswordMatch: FormItemRule['validator'] = (_rule, value, callback) => {
    if (value !== form.password) {
        callback(new Error('Passwords do not match'))
    } else {
        callback()
    }
}

const rules = {
    password: validationRules.password(),
    confirmPassword: [
        validationRules.required('Please confirm your password'),
        { validator: validatePasswordMatch, trigger: 'blur' },
    ],
}

function handleSubmit() {
    emit('submit', {
        resetToken: props.resetToken,
        newPassword: form.password,
    })
}
</script>
