<template>
    <AuthLayout
        title="Create Provider"
        subtitle="Set up your organization to start managing vouchers"
    >
        <CreateProviderForm
            :loading="loading"
            :error="error"
            :success="success"
            @submit="handleSubmit"
        />

        <template #footer>
            <div class="text-center">
                <router-link
                    to="/providers"
                    class="text-[14px] text-accent-600 hover:text-accent-700 font-medium transition-colors"
                >
                    Back to Providers
                </router-link>
            </div>
        </template>
    </AuthLayout>
</template>

<script setup lang="ts">
import { useProvider } from '@/composables/useProvider'
import type { CreateProviderRequest } from '@/types'
import AuthLayout from '@/components/layout/AuthLayout.vue'
import CreateProviderForm from '@/components/provider/CreateProviderForm.vue'

const { loading, error, success, createProvider } = useProvider()

function handleSubmit(request: CreateProviderRequest) {
    createProvider(request)
}
</script>
