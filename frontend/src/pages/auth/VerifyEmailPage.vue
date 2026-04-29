<template>
    <div>
        <p v-if="loading">Verifying email...</p>
        <template v-else-if="success">
            <p>{{ success }}</p>
            <p>
                <RouterLink to="/login">Go to login</RouterLink>
            </p>
        </template>
        <p v-else-if="error">
            {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { verifyEmail } from '@/api/auth.api'

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
