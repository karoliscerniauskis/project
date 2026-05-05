<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Provider Invitation</h1>
                        <p class="text-slate-600 mt-1">Accept your invitation to join a provider</p>
                    </div>
                    <RouterLink
                        to="/providers"
                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors"
                    >
                        Providers
                    </RouterLink>
                </div>
            </div>

            <LoadingSpinner v-if="loading" />

            <ErrorMessage v-else-if="error" :message="error" />

            <div
                v-else-if="accepted"
                class="bg-green-50 border border-green-200 rounded-xl p-8 text-center"
            >
                <div class="mx-auto flex h-12 w-12 items-center justify-center text-4xl">✅</div>

                <h3 class="mt-4 text-lg font-semibold text-green-900">
                    Invitation accepted successfully!
                </h3>

                <p class="mt-2 text-green-700">
                    You can now access this provider from your providers list.
                </p>

                <RouterLink
                    to="/providers"
                    class="mt-6 inline-block px-6 py-3 bg-green-600 !text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                >
                    View Providers
                </RouterLink>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm p-8">
                <div class="text-center mb-8">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center text-5xl">
                        🎟️
                    </div>

                    <h2 class="mt-4 text-xl font-semibold text-slate-900">
                        Accept provider invitation?
                    </h2>

                    <p class="mt-2 text-slate-600">
                        After accepting, you will become a member of this provider.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="submitting"
                    class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium text-lg"
                    @click="onAccept"
                >
                    {{ submitting ? 'Accepting...' : 'Accept Invitation' }}
                </button>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { acceptProviderInvitation } from '@/api/provider.api'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorMessage from '@/components/common/ErrorMessage.vue'

const route = useRoute()

const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const slug = ref('')
const accepted = ref(false)

onMounted(() => {
    const routeSlug = route.params.slug

    if (typeof routeSlug !== 'string' || routeSlug.length === 0) {
        error.value = 'Invalid invitation link.'
        loading.value = false
        return
    }

    slug.value = routeSlug
    loading.value = false
})

async function onAccept(): Promise<void> {
    if (!slug.value) {
        return
    }

    submitting.value = true
    error.value = ''

    try {
        await acceptProviderInvitation(slug.value)
        accepted.value = true
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to accept invitation.'
    } finally {
        submitting.value = false
    }
}
</script>
