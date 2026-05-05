<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Verify Email</h1>
                    <p class="text-slate-600 mt-1">Confirm your email address</p>
                </div>
            </div>

            <LoadingSpinner v-if="loading" />

            <div
                v-else-if="success"
                class="bg-green-50 border border-green-200 rounded-xl p-8 text-center"
            >
                <div class="mx-auto flex h-12 w-12 items-center justify-center text-4xl">✅</div>

                <h3 class="mt-4 text-lg font-semibold text-green-900">
                    Email verified successfully!
                </h3>

                <p class="mt-2 text-green-700">You can now log in to your account.</p>

                <RouterLink
                    to="/login"
                    class="mt-6 inline-block px-6 py-3 bg-green-600 !text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                >
                    Go to Login
                </RouterLink>
            </div>

            <ErrorMessage v-else-if="error" :message="error" />
        </div>
    </main>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { verifyEmail } from '@/api/auth.api'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorMessage from '@/components/common/ErrorMessage.vue'

const route = useRoute()

const loading = ref(true)
const success = ref('')
const error = ref('')

onMounted(async () => {
    const slug = route.params.slug

    if (typeof slug !== 'string' || slug.length === 0) {
        loading.value = false
        error.value = 'Invalid verification link.'
        return
    }

    try {
        await verifyEmail(slug)
        success.value = 'Email verified successfully. You can now log in.'
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Verification failed.'
    } finally {
        loading.value = false
    }
})
</script>
