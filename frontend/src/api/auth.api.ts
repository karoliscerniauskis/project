import { apiRequest } from './http'

export type AuthCredentials = {
    email: string
    password: string
}

export type LoginResponse = {
    token: string
}

export function login(payload: AuthCredentials): Promise<LoginResponse> {
    return apiRequest<LoginResponse>('/api/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
}

export function register(payload: AuthCredentials): Promise<void> {
    return apiRequest<void>('/api/auth/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
}

export function verifyEmail(slug: string): Promise<void> {
    return apiRequest<void>(`/api/auth/verify-email/${slug}`, {
        method: 'GET',
    })
}
