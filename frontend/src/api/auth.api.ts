import { http } from '@/utils/http'
import { storage } from '@/utils/storage'
import type {
    LoginCredentials,
    RegisterCredentials,
    ForgotPasswordRequest,
    ResetPasswordRequest,
    AuthResponse,
    User,
    ApiResponse,
} from '@/types'

export const authApi = {
    async login(credentials: LoginCredentials): Promise<void> {
        const response = await http.post<AuthResponse>(
            '/api/auth/login',
            credentials,
            { skipAuth: true }
        )
        storage.setAccessToken(response.token)
        if (response.refresh_token) {
            storage.setRefreshToken(response.refresh_token)
        }
    },

    async register(credentials: RegisterCredentials): Promise<void> {
        await http.post('/api/auth/register', credentials, { skipAuth: true })
    },

    async logout(): Promise<void> {
        storage.clearTokens()
    },

    async forgotPassword(request: ForgotPasswordRequest): Promise<void> {
        await http.post('/api/auth/forgot-password', { email: request.email }, { skipAuth: true })
    },

    async resetPassword(request: ResetPasswordRequest): Promise<void> {
        await http.post('/api/auth/reset-password', request, { skipAuth: true })
    },

    async getCurrentUser(): Promise<User> {
        const response = await http.get<ApiResponse<User>>('/api/auth/me')
        return response.data
    },

    async verifyEmail(token: string): Promise<void> {
        await http.post(`/api/auth/verify-email/${token}`, null, { skipAuth: true })
    },
}
