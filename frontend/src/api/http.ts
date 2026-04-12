import { refreshToken } from './auth.api'

export type ApiFieldError = {
    field: string
    message: string
}

export type ApiErrorResponse = {
    message?: string
    errors?: ApiFieldError[]
}

export class ApiError extends Error {
    public readonly status: number
    public readonly details: ApiErrorResponse | string | null

    public constructor(message: string, status: number, details: ApiErrorResponse | string | null) {
        super(message)
        this.name = 'ApiError'
        this.status = status
        this.details = details
    }
}

export async function apiRequest<TResponse>(
    url: string,
    options: ApiRequestOptions,
): Promise<TResponse> {
    const headers = new Headers(options.headers)
    const token = getAccessToken()

    if (token !== null && !headers.has('Authorization')) {
        headers.set('Authorization', `Bearer ${token}`)
    }

    const response = await fetch(url, {...options, headers})

    const contentType = response.headers.get('content-type') ?? ''
    const data = contentType.includes('application/json')
        ? await response.json().catch(() => null)
        : await response.text().catch(() => null)

    if (response.status === 401) {
        try {
            refreshPromise ??= refreshAccessToken()
            const newToken = await refreshPromise

            if (newToken !== null) {
                const retryHeaders = new Headers(options.headers)
                retryHeaders.set('Authorization', `Bearer ${newToken}`)

                const retryResponse = await fetch(url, {
                    ...options,
                    headers: retryHeaders,
                })

                return await parseResponse<TResponse>(retryResponse)
            }
        } catch {
            clearTokens()
        } finally {
            refreshPromise = null
        }

        clearTokens()
        throw new ApiError('Session expired', 401, data)
    }

    if (!response.ok) {
        const message =
            typeof data === 'string'
                ? data
                : data?.message ?? 'Request failed'

        throw new ApiError(message, response.status, data)
    }

    return data as TResponse
}

let refreshPromise: Promise<string | null> | null = null

type ApiRequestOptions = RequestInit & {
    skipRefresh?: boolean
}

function getAccessToken(): string | null {
    return localStorage.getItem('token')
}

function setAccessToken(token: string): void {
    localStorage.setItem('token', token)
}

function getRefreshToken(): string | null {
    return localStorage.getItem('refresh_token')
}

function setRefreshToken(token: string): void {
    localStorage.setItem('refresh_token', token)
}

async function refreshAccessToken(): Promise<string | null> {
    const storedRefreshToken = getRefreshToken()

    if (storedRefreshToken === null) {
        return null
    }

    const response = await refreshToken({
        refresh_token: storedRefreshToken,
    })

    setAccessToken(response.token)

    if (response.refresh_token !== undefined) {
        setRefreshToken(response.refresh_token)
    }

    return response.token
}

function clearTokens(): void {
    localStorage.removeItem('token')
    localStorage.removeItem('refresh_token')
}

async function parseResponse<TResponse>(response: Response): Promise<TResponse> {
    const contentType = response.headers.get('content-type') ?? ''
    const data = contentType.includes('application/json')
        ? await response.json().catch(() => null)
        : await response.text().catch(() => null)

    if (!response.ok) {
        const message =
            typeof data === 'string'
                ? data
                : data?.message ?? 'Request failed'

        throw new ApiError(message, response.status, data)
    }

    return data as TResponse
}
