import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authApi } from '@/api/auth.api'
import { useUser } from '@/composables/useUser'
import type {
    LoginCredentials,
    RegisterCredentials,
    ForgotPasswordRequest,
    ResetPasswordRequest,
} from '@/types'

export function useAuth() {
    const router = useRouter()
    const { clearUser } = useUser()
    const loading = ref(false)
    const error = ref<string | null>(null)
    const success = ref<string | null>(null)

    async function login(credentials: LoginCredentials) {
        loading.value = true
        error.value = null

        try {
            await authApi.login(credentials)
            await router.push('/providers')
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Login failed'
        } finally {
            loading.value = false
        }
    }

    async function register(credentials: RegisterCredentials) {
        loading.value = true
        error.value = null
        success.value = null

        try {
            await authApi.register(credentials)
            success.value = 'Account created! Please check your email to verify your account.'
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Registration failed'
        } finally {
            loading.value = false
        }
    }

    async function forgotPassword(request: ForgotPasswordRequest) {
        loading.value = true
        error.value = null
        success.value = null

        try {
            await authApi.forgotPassword(request)
            success.value = 'Password reset link has been sent to your email.'
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to send reset link'
        } finally {
            loading.value = false
        }
    }

    async function resetPassword(request: ResetPasswordRequest) {
        loading.value = true
        error.value = null
        success.value = null

        try {
            await authApi.resetPassword(request)
            success.value = 'Password reset successful!'
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to reset password'
        } finally {
            loading.value = false
        }
    }

    async function logout() {
        try {
            await authApi.logout()
        } finally {
            clearUser()
            await router.push('/login')
        }
    }

    return {
        loading,
        error,
        success,
        login,
        register,
        forgotPassword,
        resetPassword,
        logout,
    }
}
