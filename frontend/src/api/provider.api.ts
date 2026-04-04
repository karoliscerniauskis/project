import { apiRequest } from './http'

export type ProviderView = {
    id: string
    name: string
    status: string
}

export type ProvidersResponse = {
    data: ProviderView[]
}

export function getProviders(): Promise<ProvidersResponse> {
    return apiRequest<ProvidersResponse>('/api/providers', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
}
