<template>
    <BaseForm
        v-model="form"
        :rules="rules"
        :loading="loading"
        :error="error"
        :success="success"
        submit-text="Send Reset Link"
        loading-text="Sending..."
        @submit="emit('submit', $event)"
    >
        <el-alert
            type="info"
            title="Enter your email address and we'll send you a link to reset your password."
            :closable="false"
            show-icon
            class="mb-6"
        />

        <el-form-item label="Email" prop="email" required>
            <el-input
                v-model="form.email"
                type="email"
                placeholder="name@company.com"
                autocomplete="email"
                size="large"
            />
        </el-form-item>
    </BaseForm>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import type { ForgotPasswordRequest } from '@/types'
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
    submit: [request: ForgotPasswordRequest]
}>()

const form = reactive<ForgotPasswordRequest>({
    email: '',
})

const rules = {
    email: validationRules.email(),
}
</script>
