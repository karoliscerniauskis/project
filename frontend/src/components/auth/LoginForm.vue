<template>
    <form @submit.prevent="onSubmit">
        <h1>Login</h1>

        <label>
            Email
            <input v-model="email" type="email" autocomplete="email" required />
        </label>

        <label>
            Password
            <input v-model="password" type="password" autocomplete="current-password" required />
        </label>

        <button type="submit" :disabled="loading">
            {{ loading ? 'Logging in...' : 'Login' }}
        </button>

        <p v-if="error">{{ error }}</p>

        <ul v-if="fieldErrors.length">
            <li v-for="item in fieldErrors" :key="`${item.field}-${item.message}`">
                <strong>{{ item.field }}:</strong> {{ item.message }}
            </li>
        </ul>
    </form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '@/api/auth.api'
import { useFormErrors } from '@/composables/useFormErrors'
import type { ApiFieldError } from '@/api/http'

const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const fieldErrors = ref<ApiFieldError[]>([])

const { extractMessage, extractFieldErrors } = useFormErrors()

async function onSubmit() {
    loading.value = true
    error.value = ''
    fieldErrors.value = []

    try {
        const response = await login({
            email: email.value,
            password: password.value,
        })

        localStorage.setItem('token', response.token)
        await router.push('/providers')
    } catch (e) {
        error.value = extractMessage(e)
        fieldErrors.value = extractFieldErrors(e)
    } finally {
        loading.value = false
    }
}
</script>
