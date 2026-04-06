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

            <h3>Provider users</h3>
            <p v-if="usersLoading">Loading users...</p>
            <p v-else-if="usersError">{{ usersError }}</p>

            <ul v-else>
                <li v-for="item in users" :key="`${item.email}-${item.role}`">
                    {{ item.email }} — {{ item.role }}
                </li>
            </ul>
        </template>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import {
    getProvider,
    getProviderUsers,
    type ProviderView,
    type ProviderUserView,
} from '@/api/provider.api'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const provider = ref<ProviderView | null>(null)

const usersLoading = ref(true)
const usersError = ref('')
const users = ref<ProviderUserView[]>([])

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
})
</script>
