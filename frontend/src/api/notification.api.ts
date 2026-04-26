import { apiRequest } from './http'

export type NotificationView = {
    id: string
    type: string
    title: string
    message: string
    payload: Record<string, unknown>
    readAt: string | null
    createdAt: string
}

export type NotificationsResponse = {
    data: NotificationView[]
}

export type UnreadNotificationsCountResponse = {
    data: {
        count: number
    }
}

export function getMyNotifications(): Promise<NotificationsResponse> {
    return apiRequest<NotificationsResponse>('/api/me/notifications', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}

export function getUnreadNotificationsCount(): Promise<UnreadNotificationsCountResponse> {
    return apiRequest<UnreadNotificationsCountResponse>('/api/me/notifications/unread-count', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}

export function markNotificationAsRead(notificationId: string): Promise<void> {
    return apiRequest<void>(`/api/me/notifications/${encodeURIComponent(notificationId)}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}
