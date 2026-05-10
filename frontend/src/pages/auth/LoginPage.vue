<template>
    <AuthLayout title="Welcome Back" subtitle="Sign in to your account to continue">
        <LoginForm :loading="loading" :error="error" @submit="login" />

        <template #footer>
            <div class="space-y-4 text-center">
                <router-link to="/forgot-password" class="block text-[14px] text-accent-600 hover:text-accent-700 font-medium transition-colors">
                    Forgot your password?
                </router-link>
                <div class="border-t border-primary-100"></div>
                <p class="text-[14px] text-primary-600">
                    Don't have an account?
                    <router-link to="/register" class="text-accent-600 hover:text-accent-700 font-semibold transition-colors">
                        Sign up
                    </router-link>
                </p>
            </div>
        </template>
    </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '@/components/layout/AuthLayout.vue'
import LoginForm from '@/components/domain/LoginForm.vue'
import { useAuth } from '@/composables/useAuth'

const { loading, error, login } = useAuth()
</script>
