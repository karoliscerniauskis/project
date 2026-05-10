<template>
    <AuthLayout title="Reset Password" subtitle="Enter your email to receive a password reset link">
        <ForgotPasswordForm :loading="loading" :error="error" :success="success" @submit="forgotPassword" />

        <template #footer>
            <router-link to="/login" class="block text-[14px] text-accent-600 hover:text-accent-700 font-medium transition-colors text-center">
                Back to Sign In
            </router-link>
        </template>
    </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '@/components/layout/AuthLayout.vue'
import ForgotPasswordForm from '@/components/domain/ForgotPasswordForm.vue'
import { useAuth } from '@/composables/useAuth'

const { loading, error, success, forgotPassword } = useAuth()
</script>
