import { http } from '@/utils/http'
import type { CreateProviderRequest, ProvidersResponse, GetProvidersParams, Provider } from '@/types'

export interface InviteUserRequest {
    email: string
}

export interface ProviderUser {
    id: string
    email: string
    role: string
    status: string
}

export interface ProviderInvitation {
    email: string
    createdAt: string
    expiresAt: string
}

export interface ConfigureReminderSettingsRequest {
    claimReminderAfterDays: number | null
    expiryReminderBeforeDays: number | null
}

export interface LinkedProvider {
    id: string
    name: string
    status: string
}

export interface LinkProviderRequest {
    linkedProviderId: string
}

export const providerApi = {
    async createProvider(request: CreateProviderRequest): Promise<void> {
        await http.post('/api/provider', request)
    },

    async getProviders(params?: GetProvidersParams): Promise<ProvidersResponse> {
        const queryParams = new URLSearchParams()

        if (params?.page) queryParams.append('page', params.page.toString())
        if (params?.limit) queryParams.append('limit', params.limit.toString())
        if (params?.name) queryParams.append('name', params.name)
        if (params?.status) queryParams.append('status', params.status)

        const query = queryParams.toString()
        const url = query ? `/api/providers?${query}` : '/api/providers'

        return http.get<ProvidersResponse>(url)
    },

    async getAdminProviders(params?: GetProvidersParams): Promise<ProvidersResponse> {
        const queryParams = new URLSearchParams()

        if (params?.page) queryParams.append('page', params.page.toString())
        if (params?.limit) queryParams.append('limit', params.limit.toString())
        if (params?.name) queryParams.append('name', params.name)
        if (params?.status) queryParams.append('status', params.status)

        const query = queryParams.toString()
        const url = query ? `/api/admin/providers?${query}` : '/api/admin/providers'

        return http.get<ProvidersResponse>(url)
    },

    async getProvider(providerId: string): Promise<Provider> {
        const response = await http.get<{ data: Provider }>(`/api/providers/${providerId}`)
        return response.data
    },

    async getProviderUsers(providerId: string): Promise<ProviderUser[]> {
        const response = await http.get<{ data: ProviderUser[] }>(`/api/providers/${providerId}/users`)
        return response.data
    },

    async inviteUser(providerId: string, data: InviteUserRequest): Promise<void> {
        await http.post(`/api/provider/${providerId}/invite`, data)
    },

    async approveProvider(providerId: string): Promise<void> {
        await http.patch(`/api/admin/provider/${providerId}/approve`)
    },

    async deactivateProvider(providerId: string): Promise<void> {
        await http.patch(`/api/admin/provider/${providerId}/deactivate`)
    },

    async removeProviderUser(providerId: string, providerUserId: string): Promise<void> {
        await http.delete(`/api/providers/${providerId}/users/${providerUserId}`)
    },

    async getProviderInvitations(providerId: string): Promise<ProviderInvitation[]> {
        const response = await http.get<{ data: ProviderInvitation[] }>(`/api/providers/${providerId}/invitations`)
        return response.data
    },

    async cancelProviderInvitation(providerId: string, email: string): Promise<void> {
        await http.delete(`/api/providers/${providerId}/invitations/${encodeURIComponent(email)}`)
    },

    async configureReminderSettings(providerId: string, data: ConfigureReminderSettingsRequest): Promise<void> {
        await http.patch(`/api/providers/${providerId}/reminder-settings`, data)
    },

    async getLinkedProviders(providerId: string): Promise<LinkedProvider[]> {
        const response = await http.get<{ data: LinkedProvider[] }>(`/api/providers/${providerId}/linked`)
        return response.data
    },

    async linkProvider(providerId: string, data: LinkProviderRequest): Promise<void> {
        await http.post(`/api/providers/${providerId}/link`, data)
    },

    async unlinkProvider(providerId: string, linkedProviderId: string): Promise<void> {
        await http.delete(`/api/providers/${providerId}/link/${linkedProviderId}`)
    },

    async getLinkedProvidersForVoucher(voucherId: string): Promise<LinkedProvider[]> {
        const response = await http.get<{ data: LinkedProvider[] }>(`/api/vouchers/${voucherId}/linked-providers`)
        return response.data
    },

    async getAvailableProvidersToLink(providerId: string): Promise<LinkedProvider[]> {
        const response = await http.get<{ data: LinkedProvider[] }>(`/api/providers/${providerId}/available-to-link`)
        return response.data
    },
}
