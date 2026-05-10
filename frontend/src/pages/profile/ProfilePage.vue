<template>
    <div class="min-h-screen bg-primary-50">
        <TopNav />
        <div class="max-w-3xl mx-auto px-6 py-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-primary-900">Edit Profile</h1>
                <p class="mt-2 text-primary-600">Manage your account settings and preferences</p>
            </div>

            <div class="space-y-6">
                <ChangeEmailForm />
                <ChangePasswordForm />
                <EmailBreachSettings />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import TopNav from '@/components/layout/TopNav.vue'
import ChangeEmailForm from '@/components/profile/ChangeEmailForm.vue'
import ChangePasswordForm from '@/components/profile/ChangePasswordForm.vue'
import EmailBreachSettings from '@/components/profile/EmailBreachSettings.vue'
</script>
