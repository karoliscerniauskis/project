import { ref } from 'vue'
import { notificationApi } from '@/api/notification.api'

const notificationCount = ref(0)

export function useNotificationCount() {
    async function fetchCount() {
        try {
            notificationCount.value = await notificationApi.getUnreadCount()
        } catch (err) {
            console.error('Failed to fetch notification count', err)
        }
    }

    function decrementCount() {
        if (notificationCount.value > 0) {
            notificationCount.value--
        }
    }

    return {
        notificationCount,
        fetchCount,
        decrementCount,
    }
}
