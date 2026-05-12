<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-primary-900">Users</h2>
            <el-button :loading="loading" size="small" @click="loadUsers">
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

        <div v-else-if="!users || users.length === 0" class="text-center py-8">
            <p class="text-primary-600">No users found</p>
        </div>

        <div v-else class="space-y-2">
            <div
                v-for="user in users"
                :key="user.id"
                class="flex items-center justify-between p-4 rounded-lg border border-primary-100 hover:bg-primary-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <el-icon :size="20" class="text-primary-600">
                        <User />
                    </el-icon>
                    <div>
                        <p class="font-medium text-primary-900">{{ user.email }}</p>
                    </div>
                </div>

                <el-button
                    v-if="isAdmin && user.role !== 'admin'"
                    type="danger"
                    size="small"
                    @click="handleRemoveUser(user)"
                >
                    Remove
                </el-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Loading, User, Refresh } from '@element-plus/icons-vue'
import { providerApi, type ProviderUser } from '@/api/provider.api'
import { useAsyncState } from '@/composables/useAsyncState'
import { useConfirm } from '@/composables/useConfirm'
import { MESSAGES } from '@/constants/messages'

const props = defineProps<{
    providerId: string
    isAdmin?: boolean
}>()

const { data: users, loading, error, execute: fetchUsers } = useAsyncState<ProviderUser[]>()
const { confirm } = useConfirm()

async function loadUsers() {
    await fetchUsers(() => providerApi.getProviderUsers(props.providerId), {
        errorMessage: MESSAGES.ERROR.USER_LOAD,
    })
}

async function handleRemoveUser(user: ProviderUser) {
    const confirmed = await confirm(
        MESSAGES.CONFIRM.USER_REMOVE(user.email),
        'Remove User'
    )

    if (!confirmed) return

    try {
        await providerApi.removeProviderUser(props.providerId, user.id)
        ElMessage.success(MESSAGES.SUCCESS.USER_REMOVED)
        await loadUsers()
    } catch (err) {
        ElMessage.error(err instanceof Error ? err.message : MESSAGES.ERROR.USER_REMOVE)
    }
}

onMounted(() => {
    loadUsers()
})
</script>
