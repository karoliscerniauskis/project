<template>
    <div class="min-h-screen bg-primary-50">
        <TopNav />
        <div class="max-w-4xl mx-auto px-6 py-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-primary-900">Notifications</h1>
            </div>

            <div v-if="loading" class="flex justify-center items-center py-12">
                <el-icon class="is-loading" :size="32">
                    <Loading />
                </el-icon>
            </div>

            <div v-else-if="error" class="text-center py-12">
                <p class="text-red-600">{{ error }}</p>
            </div>

            <div v-else-if="!notifications || notifications.length === 0" class="text-center py-12">
                <p class="text-gray-500">No notifications</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="bg-white rounded-xl shadow-sm p-4 border border-primary-100 cursor-pointer hover:shadow-md transition-shadow"
                    :class="{ 'bg-blue-50': !notification.readAt }"
                    @click="handleNotificationClick(notification)"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-primary-900">{{ notification.title }}</h3>
                                <el-tag v-if="!notification.readAt" type="primary" size="small">New</el-tag>
                            </div>
                            <p class="text-gray-700 text-sm mb-2">{{ notification.message }}</p>
                            <p class="text-gray-500 text-xs">{{ formatRelativeDate(notification.createdAt) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Loading } from '@element-plus/icons-vue'
import { notificationApi, type Notification } from '@/api/notification.api'
import TopNav from '@/components/layout/TopNav.vue'
import { formatRelativeDate } from '@/utils/date'
import { useAsyncState } from '@/composables/useAsyncState'
import { MESSAGES } from '@/constants/messages'

const { data: notifications, loading, error, execute: fetchNotifications } = useAsyncState<Notification[]>()

async function loadNotifications() {
    await fetchNotifications(() => notificationApi.getNotifications(), {
        errorMessage: MESSAGES.ERROR.NOTIFICATION_LOAD,
    })
}

async function handleNotificationClick(notification: Notification) {
    if (!notification.readAt) {
        try {
            await notificationApi.markAsRead(notification.id)
            notification.readAt = new Date().toISOString()
        } catch (err: any) {
            ElMessage.error(err.response?.data?.message || MESSAGES.ERROR.NOTIFICATION_MARK_READ)
        }
    }
}

onMounted(() => {
    loadNotifications()
})
</script>
