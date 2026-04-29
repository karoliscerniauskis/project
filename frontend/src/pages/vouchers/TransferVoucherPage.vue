<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Transfer Voucher</h1>
                        <p class="text-slate-600 mt-1">Send this voucher to another user</p>
                    </div>
                    <RouterLink
                        to="/me/vouchers"
                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors"
                    >
                        ← Back
                    </RouterLink>
                </div>
            </div>

            <LoadingSpinner v-if="loading" />

            <ErrorMessage v-else-if="error" :message="error" />

            <div
                v-else-if="transferred"
                class="bg-green-50 border border-green-200 rounded-xl p-8 text-center"
            >
                <svg
                    class="mx-auto h-12 w-12 text-green-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-green-900">
                    Voucher transferred successfully!
                </h3>
                <p class="mt-2 text-green-700">
                    The recipient will receive a notification about the transfer.
                </p>
                <RouterLink
                    to="/me/vouchers"
                    class="mt-6 inline-block px-6 py-3 bg-green-600 !text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                >
                    View My Vouchers
                </RouterLink>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm p-8">
                <div class="mb-6">
                    <svg
                        class="mx-auto h-16 w-16 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                        />
                    </svg>
                </div>

                <div v-if="formError" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800">
                        {{ formError }}
                    </p>
                </div>

                <form class="space-y-6" @submit.prevent="onTransfer">
                    <div>
                        <label
                            for="recipientEmail"
                            class="block text-sm font-medium mb-2"
                            :class="
                                fieldErrors.find(e => e.field === 'recipientEmail')
                                    ? 'text-red-700'
                                    : 'text-slate-700'
                            "
                        >
                            Recipient Email
                        </label>
                        <input
                            id="recipientEmail"
                            v-model="recipientEmail"
                            type="text"
                            :placeholder="
                                fieldErrors.find(e => e.field === 'recipientEmail')
                                    ? 'Email is required'
                                    : 'recipient@example.com'
                            "
                            :class="
                                fieldErrors.find(e => e.field === 'recipientEmail')
                                    ? 'border-red-500 placeholder-red-400'
                                    : 'border-slate-300'
                            "
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200 transition-all"
                        />
                        <p
                            v-if="fieldErrors.find(e => e.field === 'recipientEmail')"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ fieldErrors.find(e => e.field === 'recipientEmail')?.message }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                    >
                        {{ submitting ? 'Transferring...' : 'Transfer Voucher' }}
                    </button>
                </form>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { transferVoucher } from '@/api/voucher.api'
import { useForm } from '@/composables/useForm'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorMessage from '@/components/common/ErrorMessage.vue'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const voucherId = ref('')
const recipientEmail = ref('')
const transferred = ref(false)

const { submitting, formError, fieldErrors, handleSubmit, validateRequired } = useForm()

onMounted(() => {
    const id = route.params.voucherId

    if (typeof id !== 'string' || id.length === 0) {
        error.value = 'Invalid voucher id.'
        loading.value = false
        return
    }

    voucherId.value = id
    loading.value = false
})

async function onTransfer(): Promise<void> {
    if (!voucherId.value) {
        return
    }

    if (!validateRequired('recipientEmail', recipientEmail.value, 'Email')) {
        return
    }

    await handleSubmit(
        () =>
            transferVoucher(voucherId.value, {
                recipientEmail: recipientEmail.value.trim(),
            }),
        () => {
            transferred.value = true
        }
    )
}
</script>
