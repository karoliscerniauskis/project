<template>
    <main class="min-h-screen px-6 py-10 text-slate-950 sm:px-8 lg:px-12">
        <div class="mx-auto w-full max-w-2xl">
            <div class="mb-10">
                <h1 class="text-4xl font-semibold tracking-[-0.045em] text-slate-950">
                    Create Provider
                </h1>
                <p class="mt-3 text-base leading-7 text-slate-500">
                    Add a new voucher provider to your account.
                </p>
            </div>

            <form
                novalidate
                class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm"
                @submit.prevent="onSubmit"
            >
                <div class="space-y-6">
                    <label class="block">
                        <span
                            class="mb-2 block text-sm font-medium"
                            :class="hasFieldError('name') ? 'text-red-700' : 'text-slate-700'"
                        >
                            Provider Name
                        </span>
                        <input
                            v-model="name"
                            type="text"
                            class="h-12 w-full rounded-2xl border bg-white px-4 text-base text-slate-950 outline-none transition"
                            :class="
                                hasFieldError('name')
                                    ? 'border-red-300 placeholder:text-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100'
                                    : 'border-slate-200 placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-950/5'
                            "
                            placeholder="Enter provider name"
                        />
                        <p v-if="getFieldError('name')" class="mt-2 text-sm text-red-600">
                            {{ getFieldError('name') }}
                        </p>
                    </label>
                </div>

                <div class="mt-8 flex items-center gap-4">
                    <button
                        type="submit"
                        :disabled="loading"
                        class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-base font-medium !text-white shadow-[0_12px_32px_rgba(15,23,42,0.18)] transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                    >
                        {{ loading ? 'Creating...' : 'Create provider' }}
                    </button>

                    <RouterLink
                        to="/providers"
                        class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 text-base font-medium text-slate-950 transition hover:border-slate-300 hover:bg-slate-50"
                    >
                        Cancel
                    </RouterLink>
                </div>

                <p
                    v-if="error"
                    class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-700"
                >
                    {{ error }}
                </p>

                <p
                    v-if="success"
                    class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm leading-6 text-green-700"
                >
                    {{ success }}
                </p>
            </form>
        </div>
    </main>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { createProvider } from '@/api/provider.api'
import { useForm } from '@/composables/useForm'

const router = useRouter()
const name = ref('')
const success = ref('')

const {
    submitting: loading,
    formError: error,
    fieldErrors: _fieldErrors,
    handleSubmit,
    validateRequired,
    hasFieldError,
    getFieldError,
} = useForm()

async function onSubmit() {
    success.value = ''

    if (!validateRequired('name', name.value, 'Name')) {
        return
    }

    await handleSubmit(
        () => createProvider({ name: name.value }),
        async () => {
            success.value = 'Provider created successfully.'
            await router.push('/providers')
        }
    )
}
</script>
