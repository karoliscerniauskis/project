<template>
    <div class="notifications-page">
        <div class="notifications-header">
            <div>
                <h1>Notifications</h1>
                <p class="muted">
                    {{ unreadCount }} unread notification{{ unreadCount === 1 ? '' : 's' }}
                </p>
            </div>

            <button type="button" @click="loadNotifications" :disabled="loading">
                Refresh
            </button>
        </div>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error" class="error">{{ error }}</p>

        <template v-else>
            <p v-if="notifications.length === 0">No notifications found.</p>

            <ul v-else class="notification-list">
                <li
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="notification-card"
                    :class="{ unread: notification.readAt === null }"
                >
                    <div class="notification-content">
                        <div class="notification-title-row">
                            <h2>{{ notification.title }}</h2>
                            <span v-if="notification.readAt === null" class="badge">Unread</span>
                        </div>

                        <p>{{ notification.message }}</p>

                        <p class="muted">
                            {{ formatDate(notification.createdAt) }}
                        </p>

                        <details v-if="Object.keys(notification.payload).length > 0">
                            <summary>Details</summary>
                            <pre>{{ notification.payload }}</pre>
                        </details>
                    </div>

                    <button
                        v-if="notification.readAt === null"
                        type="button"
                        :disabled="markingId === notification.id"
                        @click="markAsRead(notification.id)"
                    >
                        {{ markingId === notification.id ? 'Marking...' : 'Mark as read' }}
                    </button>
                </li>
            </ul>
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import {
    getMyNotifications,
    markNotificationAsRead,
    type NotificationView,
} from '@/api/notification.api'

const loading = ref(true)
const error = ref('')
const markingId = ref<string | null>(null)
const notifications = ref<NotificationView[]>([])

const unreadCount = computed(() => (
    notifications.value.filter((notification) => notification.readAt === null).length
))

onMounted(loadNotifications)

async function loadNotifications(): Promise<void> {
    loading.value = true
    error.value = ''

    try {
        const response = await getMyNotifications()
        notifications.value = response.data
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load notifications.'
    } finally {
        loading.value = false
    }
}

async function markAsRead(notificationId: string): Promise<void> {
    markingId.value = notificationId
    error.value = ''

    try {
        await markNotificationAsRead(notificationId)

        notifications.value = notifications.value.map((notification) => {
            if (notification.id !== notificationId) {
                return notification
            }

            return {
                ...notification,
                readAt: new Date().toISOString(),
            }
        })
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to mark notification as read.'
    } finally {
        markingId.value = null
    }
}

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('lt-LT', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value))
}
</script>
