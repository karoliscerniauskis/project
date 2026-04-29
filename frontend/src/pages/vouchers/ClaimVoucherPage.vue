<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Claim Voucher</h1>
                        <p class="text-slate-600 mt-1">Claim this voucher to reveal its code</p>
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
                v-else-if="claimed"
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
                    Voucher claimed successfully!
                </h3>
                <p class="mt-2 text-green-700">
                    You can now view your voucher code in your vouchers list.
                </p>
                <RouterLink
                    to="/me/vouchers"
                    class="mt-6 inline-block px-6 py-3 bg-green-600 !text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                >
                    View My Vouchers
                </RouterLink>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm p-8">
                <div class="text-center mb-8">
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
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <h2 class="mt-4 text-xl font-semibold text-slate-900">
                        Ready to claim this voucher?
                    </h2>
                    <p class="mt-2 text-slate-600">
                        Once claimed, the voucher code will be revealed to you.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="submitting"
                    class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium text-lg"
                    @click="onClaim"
                >
                    {{ submitting ? 'Claiming...' : 'Claim Voucher' }}
                </button>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { claimVoucher } from '@/api/voucher.api'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ErrorMessage from '@/components/common/ErrorMessage.vue'

const route = useRoute()
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const voucherId = ref('')
const claimed = ref(false)

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

async function onClaim(): Promise<void> {
    if (!voucherId.value) {
        return
    }

    submitting.value = true
    error.value = ''

    try {
        await claimVoucher(voucherId.value)
        claimed.value = true
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to claim voucher.'
    } finally {
        submitting.value = false
    }
}
</script>
