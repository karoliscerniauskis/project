<template>
    <AuthLayout title="Email Verification" subtitle="Verifying your email address">
        <div class="text-center py-8">
            <div v-if="loading" class="space-y-4">
                <el-icon class="is-loading text-accent-600" :size="48">
                    <Loading />
                </el-icon>
                <p class="text-primary-600">Verifying your email...</p>
            </div>

            <div v-else-if="success" class="space-y-4">
                <div class="flex justify-center">
                    <el-icon :size="48" style="color: #16a34a;">
                        <CircleCheck />
                    </el-icon>
                </div>
                <div class="space-y-2">
                    <p class="text-lg font-semibold text-primary-900">Email Verified Successfully!</p>
                    <p class="text-primary-600">You can now log in to your account.</p>
                </div>
                <el-button type="primary" size="large" @click="navigateToLogin">
                    Go to Login
                </el-button>
            </div>

            <div v-else-if="error" class="space-y-4">
                <div class="flex justify-center">
                    <el-icon :size="48" style="color: #dc2626;">
                        <CircleClose />
                    </el-icon>
                </div>
                <div class="space-y-2">
                    <p class="text-lg font-semibold text-primary-900">Verification Failed</p>
                    <p class="text-primary-600">{{ error }}</p>
                </div>
                <el-button type="primary" size="large" @click="navigateToLogin">
                    Back to Login
                </el-button>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Loading, CircleCheck, CircleClose } from '@element-plus/icons-vue'
import AuthLayout from '@/components/layout/AuthLayout.vue'
import { http } from '@/utils/http'
import { useAsyncAction } from '@/composables/useAsyncState'

const router = useRouter()
const route = useRoute()

const { loading, error, execute } = useAsyncAction()
const success = ref(false)

async function verifyEmail(token: string) {
    await execute(
        async () => {
            await http.get(`/api/auth/verify-email/${token}`, { skipAuth: true })
            success.value = true
        },
        {
            errorMessage: 'Verification failed. The link may be invalid or expired.',
        }
    )
}

function navigateToLogin() {
    router.push('/login')
}

onMounted(() => {
    const token = route.params.token as string
    if (token) {
        verifyEmail(token)
    } else {
        error.value = 'Invalid verification link'
    }
})
</script>
