<template>
    <main class="min-h-screen px-6 py-10 text-slate-950 sm:px-8 lg:px-12">
        <div class="mx-auto w-full max-w-2xl">
            <div v-if="loading" class="flex items-center justify-center py-20">
                <p class="text-base text-slate-500">Loading...</p>
            </div>

            <div
                v-else-if="error"
                class="rounded-2xl border border-red-200 bg-red-50 px-6 py-4 text-sm text-red-700"
            >
                {{ error }}
            </div>

            <template v-else>
                <div class="mb-10">
                    <h1 class="text-4xl font-semibold tracking-[-0.045em] text-slate-950">
                        Invite Provider User
                    </h1>
                    <p class="mt-3 text-base leading-7 text-slate-500">
                        Send an invitation to a user to join this provider.
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
                                :class="
                                    fieldErrors.some(e => e.field === 'email')
                                        ? 'text-red-700'
                                        : 'text-slate-700'
                                "
                            >
                                Email Address
                            </span>
                            <input
                                v-model="email"
                                type="email"
                                class="h-12 w-full rounded-2xl border bg-white px-4 text-base text-slate-950 outline-none transition"
                                :class="
                                    fieldErrors.some(e => e.field === 'email')
                                        ? 'border-red-300 placeholder:text-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100'
                                        : 'border-slate-200 placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-950/5'
                                "
                                placeholder="user@example.com"
                            />
                            <p
                                v-for="item in fieldErrors.filter(e => e.field === 'email')"
                                :key="item.message"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ item.message }}
                            </p>
                        </label>
                    </div>

                    <div class="mt-8 flex items-center gap-4">
                        <button
                            type="submit"
                            :disabled="loadingInvite"
                            class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-base font-medium !text-white shadow-[0_12px_32px_rgba(15,23,42,0.18)] transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                        >
                            {{ loadingInvite ? 'Inviting...' : 'Send invitation' }}
                        </button>

                        <RouterLink
                            :to="`/providers/${providerId}`"
                            class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 text-base font-medium text-slate-950 transition hover:border-slate-300 hover:bg-slate-50"
                        >
                            Cancel
                        </RouterLink>
                    </div>

                    <div
                        v-if="inviteError || fieldErrors.some(e => e.field !== 'email')"
                        class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-700"
                    >
                        <p v-if="inviteError">
                            {{ inviteError }}
                        </p>
                        <p
                            v-for="item in fieldErrors.filter(e => e.field !== 'email')"
                            :key="`${item.field}-${item.message}`"
                        >
                            {{ item.message }}
                        </p>
                    </div>

                    <p
                        v-if="inviteSuccess"
                        class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm leading-6 text-green-700"
                    >
                        {{ inviteSuccess }}
                    </p>
                </form>
            </template>
        </div>
    </main>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getProvider, inviteProviderUser } from '@/api/provider.api'
import { useAsyncData } from '@/composables/useAsyncData'
import { useForm } from '@/composables/useForm'

const route = useRoute()
const router = useRouter()

const email = ref('')
const inviteSuccess = ref('')

const providerId = computed(() => {
    const id = route.params.id
    return typeof id === 'string' ? id : ''
})

const {
    loading,
    error,
    data: providerResponse,
} = useAsyncData(async () => {
    if (!providerId.value) {
        throw new Error('Invalid provider id.')
    }
    const response = await getProvider(providerId.value)

    if (!response.data.isAdmin) {
        await router.push(`/providers/${response.data.id}`)
    }

    return response
})

const provider = computed(() => providerResponse.value?.data ?? null)

const {
    submitting: loadingInvite,
    formError: inviteError,
    fieldErrors,
    handleSubmit,
    validateRequired,
} = useForm()

async function onSubmit() {
    if (!provider.value) {
        return
    }

    inviteSuccess.value = ''

    if (!validateRequired('email', email.value, 'Email')) {
        return
    }

    try {
        await handleSubmit(
            () => inviteProviderUser(provider.value!.id, { email: email.value }),
            () => {
                inviteSuccess.value = 'Invitation sent.'
                email.value = ''
            }
        )
    } catch {}
}
</script>
