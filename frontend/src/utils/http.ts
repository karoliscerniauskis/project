import type { ApiError, AuthResponse } from '@/types'
import { storage } from './storage'
import router from '@/router'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

interface RequestOptions extends RequestInit {
    skipAuth?: boolean
    skipRefresh?: boolean
}

class HttpClient {
    private readonly baseUrl: string

    constructor(baseUrl: string) {
        this.baseUrl = baseUrl
    }

    private async request<T>(
        endpoint: string,
        options: RequestOptions = {}
    ): Promise<T> {
        const { skipAuth, skipRefresh, headers, ...fetchOptions } = options

        const requestHeaders: Record<string, string> = {
            'Content-Type': 'application/json',
            ...(headers as Record<string, string>),
        }

        if (!skipAuth) {
            const token = storage.getAccessToken()
            if (token) {
                requestHeaders['Authorization'] = `Bearer ${token}`
            }
        }

        const response = await fetch(`${this.baseUrl}${endpoint}`, {
            ...fetchOptions,
            headers: requestHeaders,
        })

        if (response.status === 401 && !skipRefresh && !skipAuth) {
            const refreshed = await this.refreshToken()
            if (refreshed) {
                return this.request(endpoint, { ...options, skipRefresh: true })
            }
            storage.clearTokens()
            router.push('/login')
            throw new Error('Session expired')
        }

        if (!response.ok) {
            let errorMessage = 'Request failed'

            try {
                const error: ApiError = await response.json()
                errorMessage = error.message || errorMessage
            } catch {
                if (response.status === 401) {
                    errorMessage = 'Invalid email or password'
                } else if (response.status === 400) {
                    errorMessage = 'Invalid request'
                } else if (response.status === 500) {
                    errorMessage = 'Server error. Please try again later'
                }
            }

            throw new Error(errorMessage)
        }

        if (response.status === 204 || response.headers.get('content-length') === '0') {
            return {} as T
        }

        return response.json()
    }

    private async refreshToken(): Promise<boolean> {
        const refreshToken = storage.getRefreshToken()
        if (!refreshToken) return false

        try {
            const response = await fetch(`${this.baseUrl}/api/auth/token/refresh`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ refresh_token: refreshToken }),
            })

            if (!response.ok) return false

            const data: AuthResponse = await response.json()
            storage.setAccessToken(data.token)
            storage.setRefreshToken(data.refresh_token)
            return true
        } catch {
            return false
        }
    }

    async get<T>(endpoint: string, options?: RequestOptions): Promise<T> {
        return this.request<T>(endpoint, { ...options, method: 'GET' })
    }

    async post<T>(
        endpoint: string,
        body?: unknown,
        options?: RequestOptions
    ): Promise<T> {
        return this.request<T>(endpoint, {
            ...options,
            method: 'POST',
            body: JSON.stringify(body),
        })
    }

    async put<T>(
        endpoint: string,
        body?: unknown,
        options?: RequestOptions
    ): Promise<T> {
        return this.request<T>(endpoint, {
            ...options,
            method: 'PUT',
            body: JSON.stringify(body),
        })
    }

    async patch<T>(
        endpoint: string,
        body?: unknown,
        options?: RequestOptions
    ): Promise<T> {
        return this.request<T>(endpoint, {
            ...options,
            method: 'PATCH',
            body: JSON.stringify(body),
        })
    }

    async delete<T>(endpoint: string, options?: RequestOptions): Promise<T> {
        return this.request<T>(endpoint, { ...options, method: 'DELETE' })
    }
}

export const http = new HttpClient(API_BASE_URL)
