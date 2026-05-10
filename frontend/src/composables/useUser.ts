import { ref } from 'vue'
import { userApi } from '@/api/user.api'
import type { User } from '@/types'

const currentUser = ref<User | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

export function useUser() {
    async function fetchCurrentUser() {
        loading.value = true
        error.value = null

        try {
            currentUser.value = await userApi.getCurrentUser()
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch user'
            currentUser.value = null
        } finally {
            loading.value = false
        }
    }

    function clearUser() {
        currentUser.value = null
    }

    return {
        currentUser,
        loading,
        error,
        fetchCurrentUser,
        clearUser,
    }
}
