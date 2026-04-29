<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Validate Voucher</h1>
                        <p class="text-slate-600 mt-1">
                            Check if a voucher code is valid and ready to use
                        </p>
                    </div>
                    <RouterLink
                        :to="`/providers/${providerId}`"
                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors"
                    >
                        ← Back
                    </RouterLink>
                </div>
            </div>

            <LoadingSpinner v-if="loading" />

            <ErrorMessage v-else-if="error" :message="error" />

            <div v-else class="bg-white rounded-xl shadow-sm p-8">
                <form class="space-y-6" @submit.prevent="onSubmit">
                    <div>
                        <label for="code" class="block text-sm font-medium text-slate-700 mb-2">
                            Voucher Code
                        </label>
                        <input
                            id="code"
                            v-model="code"
                            type="text"
                            autocomplete="off"
                            placeholder="Enter voucher code"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200 transition-all"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting || !code.trim()"
                        class="w-full px-6 py-3 bg-slate-900 text-white rounded-lg hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                    >
                        {{ submitting ? 'Checking...' : 'Check Voucher' }}
                    </button>
                </form>

                <div v-if="result" class="mt-8 pt-8 border-t border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Validation Result</h2>

                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between p-4 rounded-lg"
                            :class="result.valid ? 'bg-green-50' : 'bg-red-50'"
                        >
                            <span class="text-sm font-medium text-slate-700">Valid:</span>
                            <span
                                class="text-sm font-semibold"
                                :class="result.valid ? 'text-green-800' : 'text-red-800'"
                            >
                                {{ result.valid ? 'Yes' : 'No' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                            <span class="text-sm font-medium text-slate-700">Status:</span>
                            <StatusBadge :status="result.status as VoucherStatus" />
                        </div>

                        <div
                            v-if="result.reason"
                            class="p-4 bg-amber-50 border border-amber-200 rounded-lg"
                        >
                            <span class="text-sm font-medium text-slate-700 block mb-1">
                                Reason:
                            </span>
                            <span class="text-sm text-amber-900">
                                {{ formatVoucherReason(result.reason) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { validateVoucher, type VoucherValidationView } from '@/api/voucher.api'
import { formatVoucherReason, type VoucherStatus } from '@/utils/status'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorMessage from '@/components/common/ErrorMessage.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'

const route = useRoute()

const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const providerId = ref('')
const code = ref('')
const result = ref<VoucherValidationView | null>(null)

onMounted(() => {
    const id = route.params.id

    if (typeof id !== 'string' || id.length === 0) {
        error.value = 'Invalid provider id.'
        loading.value = false
        return
    }

    providerId.value = id
    loading.value = false
})

async function onSubmit() {
    if (providerId.value.length === 0 || code.value.trim().length === 0) {
        return
    }

    submitting.value = true
    error.value = ''
    result.value = null

    try {
        const response = await validateVoucher(providerId.value, {
            code: code.value.trim(),
        })

        result.value = response.data
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to validate voucher.'
    } finally {
        submitting.value = false
    }
}
</script>
