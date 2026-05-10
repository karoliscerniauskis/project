<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-primary-900">Pending Invitations</h2>
            <el-button @click="loadInvitations" :loading="loading" size="small">
                <el-icon><Refresh /></el-icon>
            </el-button>
        </div>

        <div v-if="loading" class="flex justify-center py-8">
            <el-icon class="is-loading" :size="32">
                <Loading />
            </el-icon>
        </div>

        <div v-else-if="error" class="text-center py-8">
            <p class="text-red-600">{{ error }}</p>
        </div>

        <div v-else-if="!invitations || invitations.length === 0" class="text-center py-8">
            <p class="text-primary-600">No pending invitations</p>
        </div>

        <div v-else class="space-y-2">
            <div
                v-for="invitation in invitations"
                :key="invitation.email"
                class="flex items-center justify-between p-4 rounded-lg border border-primary-100 hover:bg-primary-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <el-icon :size="20" class="text-primary-600">
                        <Message />
                    </el-icon>
                    <div>
                        <p class="font-medium text-primary-900">{{ invitation.email }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-primary-500">
                                Invited {{ formatRelativeDate(invitation.createdAt) }}
                            </span>
                            <span class="text-xs text-primary-400">•</span>
                            <span class="text-xs text-primary-500">
                                Expires {{ formatRelativeDate(invitation.expiresAt) }}
                            </span>
                        </div>
                    </div>
                </div>

                <el-button
                    v-if="isAdmin"
                    type="danger"
                    size="small"
                    @click="handleCancelInvitation(invitation)"
                >
                    Cancel
                </el-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Loading, Message, Refresh } from '@element-plus/icons-vue'
import { providerApi, type ProviderInvitation } from '@/api/provider.api'
import { formatRelativeDate } from '@/utils/date'
import { useAsyncState } from '@/composables/useAsyncState'
import { useConfirm } from '@/composables/useConfirm'
import { MESSAGES } from '@/constants/messages'

const props = defineProps<{
    providerId: string
    isAdmin?: boolean
}>()

const { data: invitations, loading, error, execute: fetchInvitations } = useAsyncState<ProviderInvitation[]>()
const { confirm } = useConfirm()

async function loadInvitations() {
    await fetchInvitations(() => providerApi.getProviderInvitations(props.providerId), {
        errorMessage: MESSAGES.ERROR.INVITATION_LOAD,
    })
}

async function handleCancelInvitation(invitation: ProviderInvitation) {
    const confirmed = await confirm(
        MESSAGES.CONFIRM.INVITATION_CANCEL(invitation.email),
        'Cancel Invitation'
    )

    if (!confirmed) return

    try {
        await providerApi.cancelProviderInvitation(props.providerId, invitation.email)
        ElMessage.success(MESSAGES.SUCCESS.INVITATION_CANCELLED)
        await loadInvitations()
    } catch (err) {
        ElMessage.error(err instanceof Error ? err.message : MESSAGES.ERROR.INVITATION_CANCEL)
    }
}

onMounted(() => {
    loadInvitations()
})

defineExpose({
    fetchInvitations: loadInvitations,
})
</script>
