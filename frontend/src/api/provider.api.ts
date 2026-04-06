import { apiRequest } from './http'

export type ProviderView = {
    id: string
    name: string
    status: string
    isAdmin: boolean
}

export type ProviderUserView = {
    email: string
    role: string
}

export type ProviderInvitationView = {
    email: string
    createdAt: string
    expiresAt: string
}

export type ProvidersResponse = {
    data: ProviderView[]
}

export type ProviderResponse = {
    data: ProviderView
}

export type ProviderUsersResponse = {
    data: ProviderUserView[]
}

export type ProviderInvitationsResponse = {
    data: ProviderInvitationView[]
}

export type CreateProviderPayload = {
    name: string
}

export type InviteProviderUserPayload = {
    email: string
}

export function getProviders(): Promise<ProvidersResponse> {
    return apiRequest<ProvidersResponse>('/api/providers', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}

export function getProvider(id: string): Promise<ProviderResponse> {
    return apiRequest<ProviderResponse>(`/api/providers/${id}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}

export function getProviderUsers(id: string): Promise<ProviderUsersResponse> {
    return apiRequest<ProviderUsersResponse>(`/api/providers/${id}/users`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}

export function createProvider(payload: CreateProviderPayload): Promise<void> {
    return apiRequest<void>('/api/provider', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
}

export function inviteProviderUser(providerId: string, payload: InviteProviderUserPayload): Promise<void> {
    return apiRequest<void>(`/api/provider/${providerId}/invite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
}

export function getProviderInvitations(id: string): Promise<ProviderInvitationsResponse> {
    return apiRequest<ProviderInvitationsResponse>(`/api/providers/${id}/invitations`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}
