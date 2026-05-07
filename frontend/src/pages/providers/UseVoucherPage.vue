<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Use Voucher</h1>
                        <p class="text-slate-600 mt-1">
                            Enter the voucher code provided by the customer
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

            <div
                v-if="successMessage"
                class="mb-6 rounded-xl border border-green-200 bg-green-50 p-6 text-center"
            >
                <div class="mx-auto flex h-12 w-12 items-center justify-center text-4xl">✅</div>
                <h3 class="mt-4 text-lg font-semibold text-green-900">
                    Voucher used successfully
                </h3>
                <p class="mt-2 text-green-700">
                    {{ successMessage }}
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-8">
                <div class="mb-8 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center text-5xl">
                        🎟️
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        For amount vouchers, enter the amount used. For usage/subscription vouchers,
                        leave amount empty — one usage will be consumed.
                    </p>
                </div>

                <div
                    v-if="formError"
                    class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4"
                >
                    <p class="text-sm text-red-800">
                        {{ formError }}
                    </p>
                </div>

                <form class="space-y-6" @submit.prevent="onSubmit">
                    <div>
                        <label
                            for="code"
                            class="block text-sm font-medium mb-2"
                            :class="hasFieldError('code') ? 'text-red-700' : 'text-slate-700'"
                        >
                            Voucher Code
                        </label>

                        <input
                            id="code"
                            v-model="code"
                            type="text"
                            autocomplete="off"
                            placeholder="Enter voucher code"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200 transition-all"
                            :class="
                                hasFieldError('code')
                                    ? 'border-red-500 placeholder-red-400'
                                    : 'border-slate-300'
                            "
                        />

                        <p v-if="hasFieldError('code')" class="mt-1 text-sm text-red-600">
                            {{ getFieldError('code') }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="amount"
                            class="block text-sm font-medium mb-2"
                            :class="hasFieldError('amount') ? 'text-red-700' : 'text-slate-700'"
                        >
                            Used Amount
                            <span class="font-normal text-slate-400">(only for amount vouchers)</span>
                        </label>

                        <input
                            id="amount"
                            v-model="amount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            placeholder="15.00"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200 transition-all"
                            :class="
                                hasFieldError('amount')
                                    ? 'border-red-500 placeholder-red-400'
                                    : 'border-slate-300'
                            "
                        />

                        <p v-if="hasFieldError('amount')" class="mt-1 text-sm text-red-600">
                            {{ getFieldError('amount') }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <p class="text-sm leading-6 text-blue-900">
                            If this is a usage voucher, the system will reduce remaining usages by
                            one. If this is an amount voucher, the entered amount will be deducted
                            from the remaining balance.
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                    >
                        {{ submitting ? 'Using...' : 'Use Voucher' }}
                    </button>
                </form>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useVoucher } from '@/api/provider.api'
import { useForm } from '@/composables/useForm'

const props = defineProps<{
    id: string
}>()

const providerId = props.id
const code = ref('')
const amount = ref('')
const successMessage = ref('')

const {
    submitting,
    formError,
    fieldErrors,
    handleSubmit,
    validateRequired,
    hasFieldError,
    getFieldError,
    resetErrors,
} = useForm()

const amountInCents = computed(() => {
    if (String(amount.value).trim() === '') {
        return null
    }

    const normalizedAmount = String(amount.value).replace(',', '.')
    const parsedAmount = Number.parseFloat(normalizedAmount)

    if (Number.isNaN(parsedAmount)) {
        return null
    }

    return Math.round(parsedAmount * 100)
})

function addFieldError(field: string, message: string): void {
    fieldErrors.value.push({
        field,
        message,
    })
}

function validateAmountIfProvided(): boolean {
    if (String(amount.value).trim() === '') {
        return true
    }

    if (amountInCents.value === null || amountInCents.value <= 0) {
        addFieldError('amount', 'Amount must be greater than 0.')

        return false
    }

    return true
}

async function onSubmit(): Promise<void> {
    successMessage.value = ''
    resetErrors()

    const codeValid = validateRequired('code', code.value, 'Voucher code')
    const amountValid = validateAmountIfProvided()

    if (!codeValid || !amountValid) {
        return
    }

    await handleSubmit(
        () =>
            useVoucher(providerId, {
                code: code.value.trim(),
                amount: amountInCents.value,
            }),
        () => {
            successMessage.value = 'The voucher balance or usage count was updated.'
            code.value = ''
            amount.value = ''
        }
    )
}
</script>
