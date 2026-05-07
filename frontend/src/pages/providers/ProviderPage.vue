<template>
    <main class="min-h-screen px-6 py-10 text-slate-950 sm:px-8 lg:px-12">
        <div class="mx-auto w-full max-w-5xl">
            <div v-if="loading" class="flex items-center justify-center py-20">
                <p class="text-base text-slate-500">Loading...</p>
            </div>

            <div
                v-else-if="error"
                class="rounded-2xl border border-red-200 bg-red-50 px-6 py-4 text-sm text-red-700"
            >
                {{ error }}
            </div>

            <template v-else-if="provider">
                <div class="mb-10 flex items-start justify-between">
                    <div>
                        <h1 class="text-4xl font-semibold tracking-[-0.045em] text-slate-950">
                            {{ provider.name }}
                        </h1>
                        <p class="mt-2 inline-flex items-center gap-2 text-base">
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
                    </div>
                </div>

                <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <RouterLink
                        v-if="provider.isAdmin"
                        :to="`/providers/${provider.id}/invite`"
                        class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md"
                    >
                        <span class="text-slate-950">Invite provider user</span>
                    </RouterLink>

                    <RouterLink
                        :to="`/providers/${provider.id}/vouchers/create`"
                        class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md"
                    >
                        <span class="text-slate-950">Create voucher</span>
                    </RouterLink>

                    <RouterLink
                        :to="`/providers/${provider.id}/vouchers`"
                        class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md"
                    >
                        <span class="text-slate-950">View vouchers</span>
                    </RouterLink>

                    <RouterLink
                        :to="`/providers/${provider.id}/vouchers/validate`"
                        class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md"
                    >
                        <span class="text-slate-950">Validate voucher</span>
                    </RouterLink>

                    <RouterLink
                        :to="`/providers/${provider.id}/vouchers/use`"
                        class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md"
                    >
                        <span class="text-slate-950">Use voucher</span>
                    </RouterLink>
                </div>

                <div class="space-y-8">
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-semibold text-slate-950">Provider users</h3>

                        <div v-if="usersLoading" class="mt-4 text-sm text-slate-500">
                            Loading users...
                        </div>
                        <div
                            v-else-if="usersError"
                            class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                        >
                            {{ usersError }}
                        </div>
                        <p v-else-if="users.length === 0" class="mt-4 text-sm text-slate-500">
                            No provider users.
                        </p>

                        <ul v-else class="mt-4 space-y-3">
                            <li
                                v-for="item in users"
                                :key="`${item.email}-${item.role}`"
                                class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3"
                            >
                                <div>
                                    <p class="font-medium text-slate-950">
                                        {{ item.email }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        <span class="capitalize">{{ item.role }}</span>
                                        ·
                                        <span class="capitalize">{{ item.status }}</span>
                                    </p>
                                </div>
                                <button
                                    v-if="
                                        provider.isAdmin &&
                                        item.status === 'active' &&
                                        item.role !== 'admin'
                                    "
                                    type="button"
                                    :disabled="removingUserId === item.id"
                                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    @click="removeUser(item.id)"
                                >
                                    {{ removingUserId === item.id ? 'Removing...' : 'Remove' }}
                                </button>
                            </li>
                        </ul>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-xl font-semibold text-slate-950">Pending invitations</h3>

                        <div v-if="invitationsLoading" class="mt-4 text-sm text-slate-500">
                            Loading invitations...
                        </div>
                        <div
                            v-else-if="invitationsError"
                            class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                        >
                            {{ invitationsError }}
                        </div>
                        <p v-else-if="invitations.length === 0" class="mt-4 text-sm text-slate-500">
                            No pending invitations.
                        </p>

                        <ul v-else class="mt-4 space-y-3">
                            <li
                                v-for="item in invitations"
                                :key="`${item.email}-${item.createdAt}`"
                                class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3"
                            >
                                <div>
                                    <p class="font-medium text-slate-950">
                                        {{ item.email }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Created {{ formatDateLong(item.createdAt) }} · Expires
                                        {{ formatDateLong(item.expiresAt) }}
                                    </p>
                                </div>
                                <button
                                    v-if="provider.isAdmin"
                                    type="button"
                                    :disabled="cancellingEmail === item.email"
                                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    @click="cancelInvitation(item.email)"
                                >
                                    {{
                                        cancellingEmail === item.email ? 'Cancelling...' : 'Cancel'
                                    }}
                                </button>
                            </li>
                        </ul>
                    </section>
                </div>
            </template>
        </div>
    </main>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import {
    removeProviderUser,
    cancelProviderInvitation,
    getProvider,
    getProviderUsers,
    getProviderInvitations,
} from '@/api/provider.api'
import { useAsyncData } from '@/composables/useAsyncData'
import { formatDateLong } from '@/utils/formatters'

const route = useRoute()
const providerId = ref('')
const cancellingEmail = ref('')
const removingUserId = ref('')

onMounted(() => {
    const id = route.params.id
    if (typeof id !== 'string' || id.length === 0) {
        providerId.value = ''
    } else {
        providerId.value = id
    }
})

const {
    loading,
    error,
    data: providerResponse,
} = useAsyncData(
    () => {
        if (!providerId.value) {
            throw new Error('Invalid provider id.')
        }
        return getProvider(providerId.value)
    },
    { immediate: false }
)

const provider = computed(() => providerResponse.value?.data ?? null)

const {
    loading: usersLoading,
    error: usersError,
    data: usersResponse,
} = useAsyncData(
    () => {
        if (!providerId.value) {
            throw new Error('Invalid provider id.')
        }
        return getProviderUsers(providerId.value)
    },
    { immediate: false }
)

const users = computed(() => usersResponse.value?.data ?? [])

const {
    loading: invitationsLoading,
    error: invitationsError,
    data: invitationsResponse,
} = useAsyncData(
    () => {
        if (!providerId.value) {
            throw new Error('Invalid provider id.')
        }
        return getProviderInvitations(providerId.value)
    },
    { immediate: false }
)

const invitations = computed(() => invitationsResponse.value?.data ?? [])

onMounted(() => {
    if (providerId.value) {
        providerResponse.value = null
        usersResponse.value = null
        invitationsResponse.value = null

        getProvider(providerId.value).then(response => {
            providerResponse.value = response
            loading.value = false
        })

        getProviderUsers(providerId.value).then(response => {
            usersResponse.value = response
            usersLoading.value = false
        })

        getProviderInvitations(providerId.value).then(response => {
            invitationsResponse.value = response
            invitationsLoading.value = false
        })
    }
})

async function cancelInvitation(email: string): Promise<void> {
    if (provider.value === null) {
        return
    }

    const confirmed = window.confirm('Are you sure you want to cancel this invitation?')

    if (!confirmed) {
        return
    }

    cancellingEmail.value = email

    try {
        await cancelProviderInvitation(provider.value.id, email)
        if (invitationsResponse.value?.data) {
            invitationsResponse.value.data = invitationsResponse.value.data.filter(
                item => item.email !== email
            )
        }
    } catch (e) {
        const message = e instanceof Error ? e.message : 'Failed to cancel invitation.'
        invitationsError.value = message
    } finally {
        cancellingEmail.value = ''
    }
}

async function removeUser(providerUserId: string): Promise<void> {
    if (provider.value === null) {
        return
    }

    const confirmed = window.confirm('Are you sure you want to remove this user from the provider?')

    if (!confirmed) {
        return
    }

    removingUserId.value = providerUserId

    try {
        await removeProviderUser(provider.value.id, providerUserId)
        if (usersResponse.value?.data) {
            usersResponse.value.data = usersResponse.value.data.filter(
                item => item.id !== providerUserId
            )
        }
    } catch (e) {
        usersError.value = e instanceof Error ? e.message : 'Failed to remove provider user.'
    } finally {
        removingUserId.value = ''
    }
}
</script>
