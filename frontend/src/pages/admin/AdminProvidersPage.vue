<template>
    <main class="min-h-screen px-6 py-10 text-slate-950 sm:px-8 lg:px-12">
        <div class="mx-auto w-full max-w-6xl">
            <div class="mb-10 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-semibold tracking-[-0.045em] text-slate-950">
                        Provider Administration
                    </h1>
                    <p class="mt-3 text-base leading-7 text-slate-500">
                        Approve pending providers and deactivate active ones.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="loading"
                    class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-medium text-white shadow-[0_12px_32px_rgba(15,23,42,0.18)] transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                    @click="loadProviders"
                >
                    Refresh
                </button>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-20">
                <p class="text-base text-slate-500">Loading...</p>
            </div>

            <div
                v-else-if="error"
                class="rounded-2xl border border-red-200 bg-red-50 px-6 py-4 text-sm text-red-700"
            >
                {{ error }}
            </div>

            <div
                v-else-if="providers.length === 0"
                class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm"
            >
                <p class="text-base text-slate-500">No providers found.</p>
            </div>

            <div
                v-else
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                            >
                                Name
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="provider in providers" :key="provider.id">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-950">
                                    {{ provider.name }}
                                </div>
                                <div class="mt-1 text-xs text-slate-400">
                                    {{ provider.id }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                    :class="{
                                        'bg-green-100 text-green-800': provider.status === 'active',
                                        'bg-yellow-100 text-yellow-800':
                                            provider.status === 'pending',
                                        'bg-red-100 text-red-800': provider.status === 'inactive',
                                    }"
                                >
                                    {{ provider.status }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        :disabled="
                                            processingId === provider.id ||
                                            provider.status === 'active'
                                        "
                                        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        @click="onApprove(provider.id)"
                                    >
                                        Approve
                                    </button>

                                    <button
                                        type="button"
                                        :disabled="
                                            processingId === provider.id ||
                                            provider.status !== 'active'
                                        "
                                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        @click="onDeactivate(provider.id)"
                                    >
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p
                v-if="actionError"
                class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-700"
            >
                {{ actionError }}
            </p>
        </div>
    </main>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { approveProvider, deactivateProvider, getAdminProviders } from '@/api/provider.api'
import { useAsyncData } from '@/composables/useAsyncData'

const processingId = ref<string | null>(null)
const actionError = ref('')

const {
    loading,
    error,
    data: providersResponse,
    refresh: loadProviders,
} = useAsyncData(() => getAdminProviders())

const providers = computed(() => providersResponse.value?.data ?? [])

async function onApprove(providerId: string): Promise<void> {
    processingId.value = providerId
    actionError.value = ''

    try {
        await approveProvider(providerId)
        await loadProviders()
    } catch (e) {
        actionError.value = e instanceof Error ? e.message : 'Failed to approve provider.'
    } finally {
        processingId.value = null
    }
}

async function onDeactivate(providerId: string): Promise<void> {
    processingId.value = providerId
    actionError.value = ''

    try {
        await deactivateProvider(providerId)
        await loadProviders()
    } catch (e) {
        actionError.value = e instanceof Error ? e.message : 'Failed to deactivate provider.'
    } finally {
        processingId.value = null
    }
}
</script>
