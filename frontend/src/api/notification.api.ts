import { http } from '@/utils/http'

export interface Notification {
    id: string
    type: string
    title: string
    message: string
    payload: Record<string, unknown>
    readAt: string | null
    createdAt: string
}

export const notificationApi = {
    async getNotifications(): Promise<Notification[]> {
        const response = await http.get<{ data: Notification[] }>('/api/me/notifications')
        return response.data
    },

    async getUnreadCount(): Promise<number> {
        const response = await http.get<{ data: { count: number } }>('/api/me/notifications/unread-count')
        return response.data.count
    },

    async markAsRead(notificationId: string): Promise<void> {
        await http.post(`/api/me/notifications/${notificationId}/read`)
    },
}
