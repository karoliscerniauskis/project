<template>
    <BaseForm
        v-model="form"
        :rules="rules"
        :loading="loading"
        :error="error"
        :success="success"
        submit-text="Create Account"
        loading-text="Creating account..."
        @submit="handleSubmit"
    >
        <el-form-item label="Email" prop="email" required>
            <el-input
                v-model="form.email"
                type="email"
                placeholder="name@company.com"
                autocomplete="email"
                size="large"
            />
        </el-form-item>

        <el-form-item label="Password" prop="password" required>
            <el-input
                v-model="form.password"
                type="password"
                placeholder="At least 8 characters"
                autocomplete="new-password"
                show-password
                size="large"
            />
        </el-form-item>

        <el-form-item label="Confirm Password" prop="confirmPassword" required>
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
import type { RegisterCredentials } from '@/types'
import BaseForm from '@/components/base/BaseForm.vue'
import { validationRules } from '@/utils/validationRules'

withDefaults(
    defineProps<{
        loading?: boolean
        error?: string | null
        success?: string | null
    }>(),
    {
        loading: false,
        error: null,
        success: null,
    }
)

const emit = defineEmits<{
    submit: [credentials: RegisterCredentials]
}>()

interface FormData extends RegisterCredentials {
    confirmPassword: string
}

const form = reactive<FormData>({
    email: '',
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
    email: validationRules.email(),
    password: validationRules.password(),
    confirmPassword: [
        validationRules.required('Please confirm your password'),
        { validator: validatePasswordMatch, trigger: 'blur' },
    ],
}

function handleSubmit(data: FormData) {
    const { confirmPassword: _confirmPassword, ...credentials } = data
    emit('submit', credentials)
}
</script>
