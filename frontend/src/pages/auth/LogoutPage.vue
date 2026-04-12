<template>
    <div>
        <p v-if="loading">Logging out...</p>
        <p v-else>{{ message }}</p>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const loading = ref(true)
const message = ref('')

onMounted(async () => {
    localStorage.removeItem('token')
    localStorage.removeItem('refresh_token')
    message.value = 'You have been logged out.'
    loading.value = false

    await router.push('/login')
})
</script>
