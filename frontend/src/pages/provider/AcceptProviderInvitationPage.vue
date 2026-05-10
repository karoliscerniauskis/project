<template>
    <AuthLayout title="Provider Invitation" subtitle="Accepting your provider invitation">
        <div class="text-center py-8">
            <div v-if="loading" class="space-y-4">
                <el-icon class="is-loading text-accent-600" :size="48">
                    <Loading />
                </el-icon>
                <p class="text-primary-600">Accepting your invitation...</p>
            </div>

            <div v-else-if="success" class="space-y-4">
                <div class="flex justify-center">
                    <el-icon :size="48" style="color: #16a34a;">
                        <CircleCheck />
                    </el-icon>
                </div>
                <div class="space-y-2">
                    <p class="text-lg font-semibold text-primary-900">Invitation Accepted!</p>
                    <p class="text-primary-600">You have been successfully added to the provider.</p>
                </div>
                <el-button type="primary" size="large" @click="navigateToProviders">
                    Go to Providers
                </el-button>
            </div>

            <div v-else-if="error" class="space-y-4">
                <div class="flex justify-center">
                    <el-icon :size="48" style="color: #dc2626;">
                        <CircleClose />
                    </el-icon>
                </div>
                <div class="space-y-2">
                    <p class="text-lg font-semibold text-primary-900">Failed to Accept Invitation</p>
                    <p class="text-primary-600">{{ error }}</p>
                </div>
                <el-button type="primary" size="large" @click="navigateToProviders">
                    Go to Providers
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

async function acceptInvitation(slug: string) {
    await execute(
        async () => {
            await http.post(`/api/provider/invitations/${slug}/accept`)
            success.value = true
        },
        {
            errorMessage: 'Failed to accept invitation. The link may be invalid or expired.',
        }
    )
}

function navigateToProviders() {
    router.push('/providers')
}

onMounted(() => {
    const slug = route.params.slug as string
    if (slug) {
        acceptInvitation(slug)
    } else {
        error.value = 'Invalid invitation link'
    }
})
</script>
