export interface Provider {
    id: string
    name: string
    status: string
    isAdmin: boolean
}

export interface CreateProviderRequest {
    name: string
}

export interface Pagination {
    page: number
    limit: number
    total: number
    totalPages: number
}

export interface ProvidersResponse {
    data: Provider[]
    pagination: Pagination
}

export interface GetProvidersParams {
    page?: number
    limit?: number
    name?: string
    status?: string
}
