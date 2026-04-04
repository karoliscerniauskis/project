<template>
    <form @submit.prevent="onSubmit">
        <h1>Register</h1>

        <label>
            Email
            <input v-model="email" type="email" autocomplete="email" required />
        </label>

        <label>
            Password
            <input v-model="password" type="password" autocomplete="new-password" required />
        </label>

        <button type="submit" :disabled="loading">
            {{ loading ? 'Creating account...' : 'Register' }}
        </button>

        <p v-if="success">{{ success }}</p>
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
import { register } from '@/api/auth.api'
import { useFormErrors } from '@/composables/useFormErrors'
import type { ApiFieldError } from '@/api/http'

const email = ref('')
const password = ref('')
const loading = ref(false)
const success = ref('')
const error = ref('')
const fieldErrors = ref<ApiFieldError[]>([])

const { extractMessage, extractFieldErrors } = useFormErrors()

async function onSubmit() {
    loading.value = true
    success.value = ''
    error.value = ''
    fieldErrors.value = []

    try {
        await register({
            email: email.value,
            password: password.value,
        })

        success.value = 'Account created successfully'
        email.value = ''
        password.value = ''
    } catch (e) {
        error.value = extractMessage(e)
        fieldErrors.value = extractFieldErrors(e)
    } finally {
        loading.value = false
    }
}
</script>
