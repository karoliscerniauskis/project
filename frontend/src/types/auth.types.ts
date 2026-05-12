export interface LoginCredentials {
    email: string
    password: string
}

export interface RegisterCredentials {
    email: string
    password: string
}

export interface ForgotPasswordRequest {
    email: string
}

export interface ResetPasswordRequest {
    resetToken: string
    newPassword: string
}

export interface AuthResponse {
    token: string
    refresh_token?: string
}

export interface User {
    id: string
    email: string
    emailVerified: boolean
    emailBreachCheckEnabled: boolean
    roles: string[]
}
