<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Notifications</h1>
                        <p class="text-slate-600 mt-1">
                            {{ unreadCount }} unread notification{{ unreadCount === 1 ? '' : 's' }}
                        </p>
                    </div>
                    <button
                        type="button"
                        :disabled="loading"
                        class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                        @click="loadNotifications"
                    >
                        Refresh
                    </button>
                </div>
            </div>

            <LoadingSpinner v-if="loading" message="Loading notifications..." />

            <ErrorMessage v-else-if="error" :message="error" />

            <EmptyState
                v-else-if="notifications.length === 0"
                title="No notifications"
                description="You're all caught up!"
                icon="🔔"
            ></EmptyState>

            <ul v-else class="space-y-4">
                <li
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="bg-white rounded-xl shadow-sm overflow-hidden transition-all"
                    :class="notification.readAt === null ? 'ring-2 ring-blue-200' : ''"
                >
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <h2 class="text-lg font-semibold text-slate-900">
                                        {{ notification.title }}
                                    </h2>
                                    <span
                                        v-if="notification.readAt === null"
                                        class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold"
                                    >
                                        Unread
                                    </span>
                                </div>
                            </div>
                            <button
                                v-if="notification.readAt === null"
                                type="button"
                                :disabled="markingId === notification.id"
                                class="ml-4 px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm font-medium"
                                @click="markAsRead(notification.id)"
                            >
                                {{ markingId === notification.id ? 'Marking...' : 'Mark as read' }}
                            </button>
                        </div>

                        <p class="text-slate-700 mb-3">
                            {{ notification.message }}
                        </p>

                        <p class="text-sm text-slate-500">
                            {{ formatDate(notification.createdAt) }}
                        </p>

                        <a
                            v-if="getPayloadString(notification.payload, 'url')"
                            :href="getPayloadString(notification.payload, 'url') ?? '/'"
                            class="mt-4 inline-block px-4 py-2 bg-slate-900 !text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium"
                        >
                            Open
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </main>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { getMyNotifications, markNotificationAsRead } from '@/api/notification.api'
import { useAsyncData } from '@/composables/useAsyncData'
import { formatDate } from '@/utils/formatters'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorMessage from '@/components/common/ErrorMessage.vue'
import EmptyState from '@/components/common/EmptyState.vue'

const markingId = ref<string | null>(null)

const {
    loading,
    error,
    data: notificationsResponse,
    refresh: loadNotifications,
} = useAsyncData(() => getMyNotifications())

const notifications = computed(() => notificationsResponse.value?.data ?? [])

const unreadCount = computed(
    () => notifications.value.filter(notification => notification.readAt === null).length
)

async function markAsRead(notificationId: string): Promise<void> {
    markingId.value = notificationId

    try {
        await markNotificationAsRead(notificationId)

        if (notificationsResponse.value?.data) {
            notificationsResponse.value.data = notificationsResponse.value.data.map(
                notification => {
                    if (notification.id !== notificationId) {
                        return notification
                    }

                    return {
                        ...notification,
                        readAt: new Date().toISOString(),
                    }
                }
            )
        }
    } catch (e) {
        console.error('Failed to mark notification as read:', e)
    } finally {
        markingId.value = null
    }
}

function getPayloadString(payload: Record<string, unknown>, key: string): string | null {
    const value = payload[key]

    return typeof value === 'string' && value.length > 0 ? value : null
}
</script>
