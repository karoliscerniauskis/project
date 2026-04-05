import { apiRequest } from './http'

export type ProviderView = {
    id: string
    name: string
    status: string
}

export type ProvidersResponse = {
    data: ProviderView[]
}

export type CreateProviderPayload = {
    name: string
}

export function getProviders(): Promise<ProvidersResponse> {
    return apiRequest<ProvidersResponse>('/api/providers', {
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
