<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h2 class="text-xl font-semibold text-primary-900 mb-2">Email Breach Monitoring</h2>
                <p class="text-sm text-primary-600 mb-4">
                    Enable periodic checks to see if your email has been involved in any data breaches
                </p>
            </div>
            <el-switch
                v-model="enabled"
                :loading="loading"
                size="large"
                @change="handleToggle"
            />
        </div>
        <el-alert
            v-if="error"
            type="error"
            :closable="false"
            class="mt-4"
        >
            {{ error }}
        </el-alert>
        <el-alert
            v-if="success"
            type="success"
            :closable="false"
            class="mt-4"
        >
            {{ success }}
        </el-alert>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { userApi } from '@/api/user.api'
import { useUser } from '@/composables/useUser'
import { useAsyncAction } from '@/composables/useAsyncState'
import { MESSAGES } from '@/constants/messages'

const { currentUser, fetchCurrentUser } = useUser()

const enabled = ref(false)
const { loading, error, success, execute } = useAsyncAction()

async function handleToggle(value: boolean) {
    const succeeded = await execute(
        async () => {
            await userApi.updateEmailBreachSettings({ enabled: value })
            await fetchCurrentUser()

            setTimeout(() => {
                success.value = null
            }, 3000)
        },
        {
            successMessage: value
                ? MESSAGES.SUCCESS.EMAIL_BREACH_ENABLED
                : MESSAGES.SUCCESS.EMAIL_BREACH_DISABLED,
            errorMessage: MESSAGES.ERROR.EMAIL_BREACH_SETTINGS,
        }
    )

    if (!succeeded) {
        enabled.value = !value
    }
}

onMounted(async () => {
    await fetchCurrentUser()
    if (currentUser.value) {
        enabled.value = currentUser.value.emailBreachCheckEnabled
    }
})
</script>
