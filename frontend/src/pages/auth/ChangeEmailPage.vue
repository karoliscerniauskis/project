<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Change Email</h1>
                        <p class="text-slate-600 mt-1">
                            Update your account email address
                        </p>
                    </div>

                    <RouterLink
                        to="/providers"
                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors"
                    >
                        ← Back
                    </RouterLink>
                </div>
            </div>

            <div
                v-if="requested"
                class="bg-green-50 border border-green-200 rounded-xl p-8 text-center"
            >
                <div class="mx-auto flex h-12 w-12 items-center justify-center text-4xl">📬</div>
                <h3 class="mt-4 text-lg font-semibold text-green-900">
                    Verification email sent
                </h3>
                <p class="mt-2 text-green-700">
                    Please confirm the new email address. After confirmation, vouchers issued to
                    your previous email will be moved to the new one.
                </p>
                <RouterLink
                    to="/providers"
                    class="mt-6 inline-block px-6 py-3 bg-green-600 !text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                >
                    Continue
                </RouterLink>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm p-8">
                <div
                    class="text-center mb-8 mx-auto flex h-16 w-16 items-center justify-center text-5xl"
                >
                    ✉️
                </div>

                <div v-if="formError" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800">
                        {{ formError }}
                    </p>
                </div>

                <form class="space-y-6" @submit.prevent="onSubmit">
                    <div>
                        <label
                            for="newEmail"
                            class="block text-sm font-medium mb-2"
                            :class="
                                fieldErrors.find(e => e.field === 'newEmail')
                                    ? 'text-red-700'
                                    : 'text-slate-700'
                            "
                        >
                            New Email
                        </label>

                        <input
                            id="newEmail"
                            v-model="newEmail"
                            type="email"
                            :placeholder="
                                fieldErrors.find(e => e.field === 'newEmail')
                                    ? 'New email is required'
                                    : 'new.email@example.com'
                            "
                            :class="
                                fieldErrors.find(e => e.field === 'newEmail')
                                    ? 'border-red-500 placeholder-red-400'
                                    : 'border-slate-300'
                            "
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200 transition-all"
                        />

                        <p
                            v-if="fieldErrors.find(e => e.field === 'newEmail')"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ fieldErrors.find(e => e.field === 'newEmail')?.message }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <p class="text-sm leading-6 text-blue-900">
                            We will send a verification link to your new email address. The email
                            will only be changed after confirmation.
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                    >
                        {{ submitting ? 'Sending...' : 'Send Verification Email' }}
                    </button>
                </form>
            </div>
        </div>
    </main>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { changeEmail } from '@/api/auth.api'
import { useForm } from '@/composables/useForm'

const newEmail = ref('')
const requested = ref(false)

const { submitting, formError, fieldErrors, handleSubmit, validateRequired } = useForm()

async function onSubmit(): Promise<void> {
    if (!validateRequired('newEmail', newEmail.value, 'New email')) {
        return
    }

    await handleSubmit(
        () =>
            changeEmail({
                newEmail: newEmail.value.trim(),
            }),
        () => {
            requested.value = true
        }
    )
}
</script>
