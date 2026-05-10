<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-primary-900">Invite User</h2>
        </div>

        <BaseForm
            v-model="form"
            :rules="rules"
            :loading="loading"
            :error="error"
            :success="success"
            submit-text="Send Invitation"
            @submit="handleSubmit"
        >
            <el-form-item label="Email Address" prop="email" required>
                <el-input
                    v-model="form.email"
                    type="email"
                    placeholder="Enter user email to invite"
                    size="large"
                />
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

const emit = defineEmits<{
    invited: []
}>()

interface FormData {
    email: string
}

const form = ref<FormData>({
    email: '',
})

const rules = {
    email: validationRules.email(),
}

const { loading, error, success, execute } = useAsyncAction()

async function handleSubmit() {
    await execute(
        async () => {
            await providerApi.inviteUser(props.providerId, form.value)
            form.value.email = ''
            emit('invited')
        },
        {
            successMessage: MESSAGES.SUCCESS.USER_INVITED,
            errorMessage: MESSAGES.ERROR.USER_INVITE,
        }
    )
}
</script>
