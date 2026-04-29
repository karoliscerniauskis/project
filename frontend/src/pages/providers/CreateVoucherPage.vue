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
                        Create Voucher
                    </h1>
                    <p class="mt-3 text-base leading-7 text-slate-500">
                        Issue a new voucher for
                        <strong class="font-semibold text-slate-950">
                            {{ provider?.data?.name }}
                        </strong>
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
                                    hasFieldError('issuedToEmail')
                                        ? 'text-red-700'
                                        : 'text-slate-700'
                                "
                            >
                                Issued to Email
                            </span>
                            <input
                                v-model="issuedToEmail"
                                type="email"
                                autocomplete="email"
                                class="h-12 w-full rounded-2xl border bg-white px-4 text-base text-slate-950 outline-none transition"
                                :class="
                                    hasFieldError('issuedToEmail')
                                        ? 'border-red-300 placeholder:text-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100'
                                        : 'border-slate-200 placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-950/5'
                                "
                                placeholder="recipient@example.com"
                            />
                            <p
                                v-if="hasFieldError('issuedToEmail')"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ getFieldError('issuedToEmail') }}
                            </p>
                        </label>
                    </div>

                    <div class="mt-8 flex items-center gap-4">
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-base font-medium !text-white shadow-[0_12px_32px_rgba(15,23,42,0.18)] transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                        >
                            {{ submitting ? 'Creating...' : 'Create voucher' }}
                        </button>

                        <RouterLink
                            :to="`/providers/${providerId}`"
                            class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 text-base font-medium text-slate-950 transition hover:border-slate-300 hover:bg-slate-50"
                        >
                            Cancel
                        </RouterLink>
                    </div>

                    <p
                        v-if="formError && fieldErrors.length === 0"
                        class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-700"
                    >
                        {{ formError }}
                    </p>

                    <p
                        v-if="successMessage"
                        class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm leading-6 text-green-700"
                    >
                        {{ successMessage }}
                    </p>
                </form>
            </template>
        </div>
    </main>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { getProvider, createVoucher } from '@/api/provider.api'
import { useForm } from '@/composables/useForm'
import { useAsyncData } from '@/composables/useAsyncData'

const props = defineProps<{
    id: string
}>()

const router = useRouter()
const providerId = props.id
const issuedToEmail = ref('')
const successMessage = ref('')

const { loading, error, data: provider } = useAsyncData(() => getProvider(providerId))

const {
    submitting,
    formError,
    fieldErrors,
    handleSubmit,
    validateRequired,
    hasFieldError,
    getFieldError,
} = useForm()

async function onSubmit(): Promise<void> {
    successMessage.value = ''

    if (!validateRequired('issuedToEmail', issuedToEmail.value, 'Email')) {
        return
    }

    await handleSubmit(
        () => createVoucher(providerId, { issuedToEmail: issuedToEmail.value }),
        async () => {
            successMessage.value = 'Voucher created successfully.'
            issuedToEmail.value = ''
            await router.push(`/providers/${providerId}`)
        }
    )
}
</script>
