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

                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="space-y-6">
                        <div>
                            <label
                                for="issuedToEmail"
                                class="mb-2 block text-sm font-medium"
                                :class="
                                    hasFieldError('issuedToEmail')
                                        ? 'text-red-700'
                                        : 'text-slate-700'
                                "
                            >
                                Issued to Email
                            </label>

                            <input
                                id="issuedToEmail"
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
                                @keydown.enter.prevent="onSubmit"
                            />

                            <p
                                v-if="hasFieldError('issuedToEmail')"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ getFieldError('issuedToEmail') }}
                            </p>
                        </div>

                        <div>
                            <span class="mb-2 block text-sm font-medium text-slate-700">
                                Voucher Type
                            </span>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label
                                    class="cursor-pointer rounded-2xl border p-4 transition"
                                    :class="
                                        voucherType === 'amount'
                                            ? 'border-slate-950 bg-slate-950 text-white'
                                            : 'border-slate-200 bg-white text-slate-950 hover:border-slate-300'
                                    "
                                >
                                    <input
                                        v-model="voucherType"
                                        type="radio"
                                        value="amount"
                                        class="sr-only"
                                    />
                                    <span class="block text-sm font-semibold">Amount voucher</span>
                                    <span
                                        class="mt-1 block text-sm"
                                        :class="
                                            voucherType === 'amount'
                                                ? 'text-white/70'
                                                : 'text-slate-500'
                                        "
                                    >
                                        Voucher has a fixed money amount.
                                    </span>
                                </label>

                                <label
                                    class="cursor-pointer rounded-2xl border p-4 transition"
                                    :class="
                                        voucherType === 'usage'
                                            ? 'border-slate-950 bg-slate-950 text-white'
                                            : 'border-slate-200 bg-white text-slate-950 hover:border-slate-300'
                                    "
                                >
                                    <input
                                        v-model="voucherType"
                                        type="radio"
                                        value="usage"
                                        class="sr-only"
                                    />
                                    <span class="block text-sm font-semibold">Usage voucher</span>
                                    <span
                                        class="mt-1 block text-sm"
                                        :class="
                                            voucherType === 'usage'
                                                ? 'text-white/70'
                                                : 'text-slate-500'
                                        "
                                    >
                                        Voucher can be used multiple times.
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div v-if="voucherType === 'amount'">
                            <label
                                for="amount"
                                class="mb-2 block text-sm font-medium"
                                :class="hasFieldError('amount') ? 'text-red-700' : 'text-slate-700'"
                            >
                                Amount
                            </label>

                            <input
                                id="amount"
                                v-model="amount"
                                type="number"
                                min="0.01"
                                step="0.01"
                                class="h-12 w-full rounded-2xl border bg-white px-4 text-base text-slate-950 outline-none transition"
                                :class="
                                    hasFieldError('amount')
                                        ? 'border-red-300 placeholder:text-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100'
                                        : 'border-slate-200 placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-950/5'
                                "
                                placeholder="50.00"
                                @keydown.enter.prevent="onSubmit"
                            />

                            <p v-if="hasFieldError('amount')" class="mt-2 text-sm text-red-600">
                                {{ getFieldError('amount') }}
                            </p>
                        </div>

                        <div v-if="voucherType === 'usage'">
                            <label
                                for="usages"
                                class="mb-2 block text-sm font-medium"
                                :class="hasFieldError('usages') ? 'text-red-700' : 'text-slate-700'"
                            >
                                Number of usages
                            </label>

                            <input
                                id="usages"
                                v-model="usages"
                                type="number"
                                min="1"
                                step="1"
                                class="h-12 w-full rounded-2xl border bg-white px-4 text-base text-slate-950 outline-none transition"
                                :class="
                                    hasFieldError('usages')
                                        ? 'border-red-300 placeholder:text-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100'
                                        : 'border-slate-200 placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-950/5'
                                "
                                placeholder="10"
                                @keydown.enter.prevent="onSubmit"
                            />

                            <p v-if="hasFieldError('usages')" class="mt-2 text-sm text-red-600">
                                {{ getFieldError('usages') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center gap-4">
                        <button
                            type="button"
                            :disabled="submitting"
                            class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-base font-medium !text-white shadow-[0_12px_32px_rgba(15,23,42,0.18)] transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                            @click="onSubmit"
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

                    <div
                        v-if="formError || fieldErrors.length > 0"
                        class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-700"
                    >
                        <p v-if="formError">
                            {{ formError }}
                        </p>

                        <p v-for="item in fieldErrors" :key="`${item.field}-${item.message}`">
                            {{ item.message }}
                        </p>
                    </div>

                    <p
                        v-if="successMessage"
                        class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm leading-6 text-green-700"
                    >
                        {{ successMessage }}
                    </p>
                </div>
            </template>
        </div>
    </main>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
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
const voucherType = ref<'amount' | 'usage'>('amount')
const amount = ref('')
const usages = ref('')

const amountInCents = computed(() => {
    const normalizedAmount = String(amount.value).replace(',', '.')
    const parsedAmount = Number.parseFloat(normalizedAmount)

    if (Number.isNaN(parsedAmount)) {
        return null
    }

    return Math.round(parsedAmount * 100)
})

const usagesCount = computed(() => {
    const parsedUsages = Number.parseInt(String(usages.value), 10)

    if (Number.isNaN(parsedUsages)) {
        return null
    }

    return parsedUsages
})

const { loading, error, data: provider } = useAsyncData(() => getProvider(providerId))

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

function addFieldError(field: string, message: string): void {
    fieldErrors.value.push({
        field,
        message,
    })
}

function validateEmail(): boolean {
    if (!validateRequired('issuedToEmail', issuedToEmail.value, 'Email')) {
        return false
    }

    const email = issuedToEmail.value.trim()

    if (!email.includes('@')) {
        addFieldError('issuedToEmail', 'Email must be valid.')

        return false
    }

    return true
}

function validateAmount(): boolean {
    if (!validateRequired('amount', amount.value, 'Amount')) {
        return false
    }

    if (amountInCents.value === null || amountInCents.value <= 0) {
        addFieldError('amount', 'Amount must be greater than 0.')

        return false
    }

    return true
}

function validateUsages(): boolean {
    if (!validateRequired('usages', usages.value, 'Usages')) {
        return false
    }

    if (usagesCount.value === null || usagesCount.value <= 0) {
        addFieldError('usages', 'Usages must be greater than 0.')

        return false
    }

    return true
}

async function onSubmit(): Promise<void> {
    successMessage.value = ''
    resetErrors()

    const emailValid = validateEmail()
    const voucherValueValid = voucherType.value === 'amount' ? validateAmount() : validateUsages()

    if (!emailValid || !voucherValueValid) {
        return
    }

    await handleSubmit(
        () =>
            createVoucher(providerId, {
                issuedToEmail: issuedToEmail.value.trim(),
                type: voucherType.value,
                amount: voucherType.value === 'amount' ? amountInCents.value : null,
                usages: voucherType.value === 'usage' ? usagesCount.value : null,
            }),
        async () => {
            successMessage.value = 'Voucher created successfully.'
            issuedToEmail.value = ''
            amount.value = ''
            usages.value = ''
            await router.push(`/providers/${providerId}`)
        }
    )
}
</script>
