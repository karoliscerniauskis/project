<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <el-form
            ref="formRef"
            :model="modelValue"
            :rules="rules"
            :label-position="labelPosition"
            class="space-y-4"
            @submit.prevent="handleSubmit"
        >
            <slot :form="formRef" />
        </el-form>

        <el-button
            type="primary"
            native-type="submit"
            :loading="loading"
            :disabled="loading"
            size="large"
            class="w-full"
        >
            {{ loading ? loadingText : submitText }}
        </el-button>

        <el-alert
            v-if="error"
            type="error"
            :title="error"
            :closable="false"
            show-icon
        />

        <el-alert
            v-if="success"
            type="success"
            :title="success"
            :closable="false"
            show-icon
        />
    </form>
</template>

<script setup lang="ts" generic="T extends Record<string, any>">
import { ref } from 'vue'
import type { FormInstance, FormRules } from 'element-plus'

const props = withDefaults(
    defineProps<{
        modelValue: T
        rules: FormRules<T>
        loading?: boolean
        error?: string | null
        success?: string | null
        submitText?: string
        loadingText?: string
        labelPosition?: 'left' | 'right' | 'top'
    }>(),
    {
        loading: false,
        error: null,
        success: null,
        submitText: 'Submit',
        loadingText: 'Submitting...',
        labelPosition: 'top',
    }
)

const emit = defineEmits<{
    submit: [values: T]
}>()

const formRef = ref<FormInstance>()

async function handleSubmit() {
    if (!formRef.value) return

    const valid = await formRef.value.validate().catch(() => false)
    if (valid) {
        emit('submit', props.modelValue)
    }
}

defineExpose({
    formRef,
    validate: () => formRef.value?.validate(),
    resetFields: () => formRef.value?.resetFields(),
    clearValidate: () => formRef.value?.clearValidate(),
})
</script>
