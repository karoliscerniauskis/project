<template>
    <AuthLayout title="Reset Your Password" subtitle="Enter your new password below">
        <ResetPasswordForm
            :loading="loading"
            :error="error"
            :success="success"
            :reset-token="resetToken"
            @submit="resetPassword"
        />

        <template #footer>
            <router-link to="/login" class="block text-[14px] text-accent-600 hover:text-accent-700 font-medium transition-colors text-center">
                Back to Sign In
            </router-link>
        </template>
    </AuthLayout>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import AuthLayout from '@/components/layout/AuthLayout.vue'
import ResetPasswordForm from '@/components/domain/ResetPasswordForm.vue'
import { useAuth } from '@/composables/useAuth'

const route = useRoute()
const resetToken = route.params.token as string

const { loading, error, success, resetPassword } = useAuth()
</script>
