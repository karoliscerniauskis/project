<template>
    <AuthLayout title="Create Account" subtitle="Join us today and start managing your vouchers">
        <RegisterForm :loading="loading" :error="error" :success="success" @submit="register" />

        <template #footer>
            <p class="text-[14px] text-primary-600 text-center">
                Already have an account?
                <router-link to="/login" class="text-accent-600 hover:text-accent-700 font-semibold transition-colors">
                    Sign in
                </router-link>
            </p>
        </template>
    </AuthLayout>
</template>

<script setup lang="ts">
import AuthLayout from '@/components/layout/AuthLayout.vue'
import RegisterForm from '@/components/domain/RegisterForm.vue'
import { useAuth } from '@/composables/useAuth'

const { loading, error, success, register } = useAuth()
</script>
