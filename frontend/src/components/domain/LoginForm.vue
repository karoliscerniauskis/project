<template>
    <BaseForm
        v-model="form"
        :rules="rules"
        :loading="loading"
        :error="error"
        submit-text="Sign In"
        loading-text="Signing in..."
        @submit="emit('submit', $event)"
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
                placeholder="••••••••"
                autocomplete="current-password"
                show-password
                size="large"
            />
        </el-form-item>
    </BaseForm>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import type { LoginCredentials } from '@/types'
import BaseForm from '@/components/base/BaseForm.vue'
import { validationRules } from '@/utils/validationRules'

withDefaults(
    defineProps<{
        loading?: boolean
        error?: string | null
    }>(),
    {
        loading: false,
        error: null,
    }
)

const emit = defineEmits<{
    submit: [credentials: LoginCredentials]
}>()

const form = reactive<LoginCredentials>({
    email: '',
    password: '',
})

const rules = {
    email: validationRules.email(),
    password: validationRules.password(),
}
</script>
