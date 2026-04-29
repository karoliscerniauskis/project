<template>
    <form class="mx-auto w-full max-w-md" novalidate @submit.prevent="onSubmit">
        <div class="mb-10 text-center">
            <p class="text-sm font-medium uppercase tracking-[0.24em] text-slate-400">
                Welcome back
            </p>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.045em] text-slate-950 sm:text-5xl">
                Sign in
            </h1>
            <p class="mt-4 text-base leading-7 text-slate-500">
                Access your vouchers and continue managing your digital gifts.
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
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    />
                    <button
                        type="button"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                        tabindex="-1"
                        @click="showPassword = !showPassword"
                    >
                        <svg
                            v-if="!showPassword"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                            />
                        </svg>
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
            {{ submitting ? 'Logging in...' : 'Login' }}
        </button>

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
import { useRouter } from 'vue-router'
import { login } from '@/api/auth.api'
import { useForm } from '@/composables/useForm'

const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)

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
    const isEmailValid = validateRequired('email', email.value, 'Email')
    const isPasswordValid = validateRequired('password', password.value, 'Password')

    if (!isEmailValid || !isPasswordValid) {
        return
    }

    await handleSubmit(
        () => login({ email: email.value, password: password.value }),
        async response => {
            localStorage.setItem('token', response.token)
            localStorage.setItem('refresh_token', response.refresh_token)
            await router.push('/providers')
        }
    )
}
</script>
