import { apiRequest } from './http'

export type AuthCredentials = {
    email: string
    password: string
}

export type LoginResponse = {
    token: string
    refresh_token: string
}

export type RefreshResponse = {
    token: string
    refresh_token?: string
}

export function login(payload: AuthCredentials): Promise<LoginResponse> {
    return apiRequest<LoginResponse>('/api/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
        skipRefresh: true,
        skipAuth: true,
    })
}

export function register(payload: AuthCredentials): Promise<void> {
    return apiRequest<void>('/api/auth/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
        skipRefresh: true,
        skipAuth: true,
    })
}

export function verifyEmail(slug: string): Promise<void> {
    return apiRequest<void>(`/api/auth/verify-email/${slug}`, {
        method: 'GET',
    })
}

export async function refreshToken(payload: { refresh_token: string }): Promise<RefreshResponse> {
    const response = await fetch('/api/auth/token/refresh', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })

    const data = await response.json().catch(() => null)

    if (!response.ok) {
        const message = typeof data === 'string' ? data : (data?.message ?? 'Request failed')
        throw new Error(message)
    }

    return data as RefreshResponse
}
