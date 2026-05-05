<template>
    <form class="mx-auto w-full max-w-md" novalidate @submit.prevent="onSubmit">
        <div class="mb-10 text-center">
            <p class="text-sm font-medium uppercase tracking-[0.24em] text-slate-400">
                Get started
            </p>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.045em] text-slate-950 sm:text-5xl">
                Create account
            </h1>
            <p class="mt-4 text-base leading-7 text-slate-500">
                Join our platform and start managing your digital vouchers today.
            </p>
        </div>

        <div class="space-y-5">
            <label class="block">
                <span
                    class="mb-2 block text-sm font-medium"
                    :class="hasFieldError('email') ? 'text-red-700' : 'text-slate-700'"
                >
                    Email
                </span>
                <input
                    v-model="email"
                    class="h-12 w-full rounded-2xl border bg-white px-4 text-base text-slate-950 outline-none transition"
                    :class="
                        hasFieldError('email')
                            ? 'border-red-300 placeholder:text-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100'
                            : 'border-slate-200 placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-950/5'
                    "
                    type="email"
                    autocomplete="email"
                    placeholder="you@example.com"
                />
                <p v-if="hasFieldError('email')" class="mt-2 text-sm text-red-600">
                    {{ getFieldError('email') }}
                </p>
            </label>

            <label class="block">
                <span
                    class="mb-2 block text-sm font-medium"
                    :class="hasFieldError('password') ? 'text-red-700' : 'text-slate-700'"
                >
                    Password
                </span>
                <div class="relative">
                    <input
                        v-model="password"
                        class="h-12 w-full rounded-2xl border bg-white px-4 pr-12 text-base text-slate-950 outline-none transition"
                        :class="
                            hasFieldError('password')
                                ? 'border-red-300 placeholder:text-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100'
                                : 'border-slate-200 placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-950/5'
                        "
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        placeholder="Choose a strong password"
                    />
                    <button
                        type="button"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                        tabindex="-1"
                        @click="showPassword = !showPassword"
                    >
                        <span class="text-lg" aria-hidden="true">
                            {{ showPassword ? '🙈' : '👁️' }}
                        </span>
                        <span class="sr-only">
                            {{ showPassword ? 'Hide password' : 'Show password' }}
                        </span>
                    </button>
                </div>
                <p v-if="hasFieldError('password')" class="mt-2 text-sm text-red-600">
                    {{ getFieldError('password') }}
                </p>
            </label>
        </div>

        <button
            class="mt-7 flex h-12 w-full items-center justify-center rounded-2xl bg-slate-950 px-5 text-base font-medium text-white shadow-[0_12px_32px_rgba(15,23,42,0.18)] transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
            type="submit"
            :disabled="submitting"
        >
            {{ submitting ? 'Creating account...' : 'Register' }}
        </button>

        <p
            v-if="success"
            class="mt-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm leading-6 text-green-700"
        >
            {{ success }}
        </p>

        <p
            v-if="formError"
            class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-700"
        >
            {{ formError }}
        </p>

        <ul
            v-if="fieldErrors.filter(e => e.field !== 'password' && e.field !== 'email').length"
            class="mt-4 space-y-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            <li
                v-for="item in fieldErrors.filter(
                    e => e.field !== 'password' && e.field !== 'email'
                )"
                :key="`${item.field}-${item.message}`"
            >
                <strong class="font-semibold">{{ item.field }}:</strong>
                {{ item.message }}
            </li>
        </ul>
    </form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { register } from '@/api/auth.api'
import { useForm } from '@/composables/useForm'

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const success = ref('')

const {
    submitting,
    formError,
    fieldErrors,
    handleSubmit,
    validateRequired,
    hasFieldError,
    getFieldError,
} = useForm()

async function onSubmit() {
    success.value = ''

    const isEmailValid = validateRequired('email', email.value, 'Email')
    const isPasswordValid = validateRequired('password', password.value, 'Password')

    if (!isEmailValid || !isPasswordValid) {
        return
    }

    await handleSubmit(
        () => register({ email: email.value, password: password.value }),
        async () => {
            success.value = 'Account created successfully'
            email.value = ''
            password.value = ''
        }
    )
}
</script>
