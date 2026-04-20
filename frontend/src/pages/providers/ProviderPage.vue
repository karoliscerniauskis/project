<template>
    <div>
        <h1>Provider</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else-if="provider">
            <h2>{{ provider.name }}</h2>
            <p>Status: {{ provider.status }}</p>

            <p v-if="provider.isAdmin">
                <RouterLink :to="`/providers/${provider.id}/invite`">Invite provider user</RouterLink>
            </p>

            <p>
                <RouterLink :to="`/providers/${provider.id}/vouchers/create`">
                    Create voucher
                </RouterLink>
            </p>

            <p>
                <RouterLink :to="`/providers/${provider.id}/vouchers`">
                    View vouchers
                </RouterLink>
            </p>

            <p>
                <RouterLink :to="`/providers/${provider.id}/vouchers/validate`">Validate voucher</RouterLink>
            </p>

            <h3>Provider users</h3>
            <p v-if="usersLoading">Loading users...</p>
            <p v-else-if="usersError">{{ usersError }}</p>
            <p v-else-if="users.length === 0">No provider users.</p>

            <ul v-else>
                <li v-for="item in users" :key="`${item.email}-${item.role}`">
                    {{ item.email }} — {{ item.role }} — {{ item.status }}
                    <button
                        v-if="provider.isAdmin && item.status === 'active' && item.role !== 'admin'"
                        type="button"
                        :disabled="removingUserId === item.id"
                        @click="removeUser(item.id)"
                    >
                        {{ removingUserId === item.id ? 'Removing...' : 'Remove from provider' }}
                    </button>
                </li>
            </ul>

            <h3>Pending invitations</h3>
            <p v-if="invitationsLoading">Loading invitations...</p>
            <p v-else-if="invitationsError">{{ invitationsError }}</p>
            <p v-else-if="invitations.length === 0">No pending invitations.</p>

            <ul v-else>
                <li v-for="item in invitations" :key="`${item.email}-${item.createdAt}`">
                    {{ item.email }} — created {{ item.createdAt }}, expires {{ item.expiresAt }}
                    <button
                        v-if="provider.isAdmin"
                        type="button"
                        :disabled="cancellingEmail === item.email"
                        @click="cancelInvitation(item.email)"
                    >
                        {{ cancellingEmail === item.email ? 'Cancelling...' : 'Cancel invitation' }}
                    </button>
                </li>
            </ul>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import {
    removeProviderUser,
    cancelProviderInvitation,
    getProvider,
    getProviderUsers,
    getProviderInvitations,
    type ProviderView,
    type ProviderUserView,
    type ProviderInvitationView,
} from '@/api/provider.api'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const provider = ref<ProviderView | null>(null)

const usersLoading = ref(true)
const usersError = ref('')
const users = ref<ProviderUserView[]>([])

const invitationsLoading = ref(true)
const invitationsError = ref('')
const invitations = ref<ProviderInvitationView[]>([])

const cancellingEmail = ref('')
const removingUserId = ref('')

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
        invitations.value = invitations.value.filter((item) => item.email !== email)
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
        users.value = users.value.filter((item) => item.id !== providerUserId)
    } catch (e) {
        usersError.value = e instanceof Error ? e.message : 'Failed to remove provider user.'
    } finally {
        removingUserId.value = ''
    }
}

onMounted(async () => {
    const id = route.params.id

    if (typeof id !== 'string' || id.length === 0) {
        loading.value = false
        error.value = 'Invalid provider id.'
        return
    }

    try {
        const response = await getProvider(id)
        provider.value = response.data
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load provider.'
    } finally {
        loading.value = false
    }

    try {
        const response = await getProviderUsers(id)
        users.value = response.data
    } catch (e) {
        usersError.value = e instanceof Error ? e.message : 'Failed to load provider users.'
    } finally {
        usersLoading.value = false
    }

    try {
        const response = await getProviderInvitations(id)
        invitations.value = response.data
    } catch (e) {
        invitationsError.value = e instanceof Error ? e.message : 'Failed to load invitations.'
    } finally {
        invitationsLoading.value = false
    }
})
</script>
