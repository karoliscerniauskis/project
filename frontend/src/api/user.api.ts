import { http } from '@/utils/http'
import type { User } from '@/types'

export interface ChangePasswordRequest {
    currentPassword: string
    newPassword: string
}

export interface ChangeEmailRequest {
    newEmail: string
}

export interface UpdateEmailBreachSettingsRequest {
    enabled: boolean
}

export const userApi = {
    async getCurrentUser(): Promise<User> {
        return http.get<User>('/api/me')
    },

    async changePassword(data: ChangePasswordRequest): Promise<void> {
        await http.post('/api/auth/change-password', data)
    },

    async changeEmail(data: ChangeEmailRequest): Promise<void> {
        await http.post('/api/auth/change-email', data)
    },

    async updateEmailBreachSettings(data: UpdateEmailBreachSettingsRequest): Promise<void> {
        await http.patch('/api/me/email-breach-check-settings', data)
    },
}
