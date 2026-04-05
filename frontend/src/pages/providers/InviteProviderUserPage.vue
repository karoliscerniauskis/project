<template>
    <div>
        <h1>Invite provider user</h1>

        <p v-if="loading">Loading...</p>
        <p v-else-if="error">{{ error }}</p>

        <template v-else>
            <p>
                <RouterLink :to="`/providers/${providerId}`">Back to provider</RouterLink>
            </p>

            <form @submit.prevent="onSubmit">
                <label>
                    Email
                    <input v-model="email" type="email" required />
                </label>

                <button type="submit" :disabled="loadingInvite">
                    {{ loadingInvite ? 'Inviting...' : 'Invite user' }}
                </button>
            </form>

            <p v-if="inviteError">{{ inviteError }}</p>
            <p v-if="inviteSuccess">{{ inviteSuccess }}</p>
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getProvider, inviteProviderUser, type ProviderView } from '@/api/provider.api'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const loadingInvite = ref(false)
const error = ref('')
const inviteError = ref('')
const inviteSuccess = ref('')
const email = ref('')
const provider = ref<ProviderView | null>(null)

const providerId = computed(() => {
    const id = route.params.id

    return typeof id === 'string' ? id : ''
})

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

        if (!provider.value.isAdmin) {
            await router.push(`/providers/${provider.value.id}`)
            return
        }
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load provider.'
    } finally {
        loading.value = false
    }
})

async function onSubmit() {
    if (!provider.value) {
        return
    }

    loadingInvite.value = true
    inviteError.value = ''
    inviteSuccess.value = ''

    try {
        await inviteProviderUser(provider.value.id, { email: email.value })
        inviteSuccess.value = 'Invitation sent.'
        email.value = ''
    } catch (e) {
        inviteError.value = e instanceof Error ? e.message : 'Failed to invite user.'
    } finally {
        loadingInvite.value = false
    }
}
</script>
