<template>
    <main class="min-h-screen px-6 py-10 text-slate-950 sm:px-8 lg:px-12">
        <div class="mx-auto w-full max-w-5xl">
            <div class="mb-10">
                <h1 class="text-4xl font-semibold tracking-[-0.045em] text-slate-950">
                    My Providers
                </h1>
                <p class="mt-3 text-base leading-7 text-slate-500">
                    Manage your voucher providers and create new ones.
                </p>
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

            <template v-else>
                <div
                    v-if="providers.length === 0"
                    class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm"
                >
                    <p class="text-base text-slate-500">You don't have any providers yet.</p>
                    <RouterLink
                        to="/provider/create"
                        class="mt-6 inline-flex h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-base font-medium !text-white shadow-[0_12px_32px_rgba(15,23,42,0.18)] transition hover:-translate-y-0.5 hover:bg-slate-800"
                    >
                        Create your first provider
                    </RouterLink>
                </div>

                <div v-else class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <RouterLink
                            v-for="provider in providers"
                            :key="provider.id"
                            :to="provider.status === 'active' ? `/providers/${provider.id}` : '#'"
                            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md"
                            :class="{
                                'pointer-events-none opacity-60': provider.status !== 'active',
                            }"
                        >
                            <h3
                                class="text-lg font-semibold text-slate-950 transition group-hover:text-slate-700"
                            >
                                {{ provider.name }}
                            </h3>
                            <p class="mt-2 inline-flex items-center gap-2 text-sm">
                                <span
                                    class="inline-block h-2 w-2 rounded-full"
                                    :class="{
                                        'bg-green-500': provider.status === 'active',
                                        'bg-yellow-500': provider.status === 'pending',
                                        'bg-red-500': provider.status === 'inactive',
                                    }"
                                ></span>
                                <span class="capitalize text-slate-600">{{ provider.status }}</span>
                            </p>
                        </RouterLink>
                    </div>

                    <div class="flex justify-center pt-4">
                        <RouterLink
                            to="/provider/create"
                            class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-base font-medium !text-white shadow-[0_12px_32px_rgba(15,23,42,0.18)] transition hover:-translate-y-0.5 hover:bg-slate-800"
                        >
                            Create new provider
                        </RouterLink>
                    </div>
                </div>
            </template>
        </div>
    </main>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { getProviders } from '@/api/provider.api'
import { useAsyncData } from '@/composables/useAsyncData'

const { loading, error, data: providersResponse } = useAsyncData(() => getProviders())

const providers = computed(() => providersResponse.value?.data ?? [])
</script>
