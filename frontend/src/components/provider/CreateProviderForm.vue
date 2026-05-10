<template>
    <BaseForm
        v-model="form"
        :rules="rules"
        :loading="loading"
        :error="error"
        :success="success"
        submit-text="Create Provider"
        loading-text="Creating..."
        @submit="emit('submit', $event)"
    >
        <el-form-item label="Provider Name" prop="name" required>
            <el-input
                v-model="form.name"
                type="text"
                placeholder="e.g., Coffee House"
                size="large"
            />
        </el-form-item>
    </BaseForm>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import type { CreateProviderRequest } from '@/types'
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
    submit: [request: CreateProviderRequest]
}>()

const form = reactive<CreateProviderRequest>({
    name: '',
})

const rules = {
    name: [
        validationRules.required('Provider name is required'),
        validationRules.min(2, 'Provider name must be at least 2 characters'),
    ],
}
</script>
