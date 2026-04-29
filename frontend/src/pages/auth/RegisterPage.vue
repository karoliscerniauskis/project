<template>
    <main class="min-h-screen px-6 py-10 text-slate-950 sm:px-8 lg:px-12">
        <div
            class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-6xl items-center justify-center"
        >
            <section
                class="grid w-full overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_24px_80px_rgba(15,23,42,0.10)] backdrop-blur-xl lg:grid-cols-[1.05fr_0.95fr]"
            >
                <div
                    class="hidden min-h-[640px] flex-col justify-between bg-slate-950 p-10 text-white lg:flex"
                >
                    <div>
                        <div
                            class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white/80 backdrop-blur"
                        >
                            Digital voucher platform
                        </div>

                        <div class="mt-24 max-w-lg">
                            <p
                                class="text-sm font-medium uppercase tracking-[0.28em] text-white/45"
                            >
                                Join our platform
                            </p>
                            <h1 class="mt-5 text-5xl font-semibold tracking-[-0.045em] text-white">
                                Start managing your vouchers today.
                            </h1>
                            <p class="mt-6 text-lg leading-8 text-white/62">
                                Create an account to claim, transfer and track your digital gift
                                vouchers seamlessly.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 text-sm text-white/70">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur"
                        >
                            <p class="font-semibold text-white">Fast</p>
                            <p class="mt-1 text-white/50">Quick setup</p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur"
                        >
                            <p class="font-semibold text-white">Secure</p>
                            <p class="mt-1 text-white/50">Protected data</p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur"
                        >
                            <p class="font-semibold text-white">Simple</p>
                            <p class="mt-1 text-white/50">Easy to use</p>
                        </div>
                    </div>
                </div>

                <div
                    class="flex min-h-[640px] flex-col justify-center px-6 py-10 sm:px-12 lg:px-16"
                >
                    <RegisterForm />

                    <p class="mt-8 text-center text-sm text-slate-500">
                        Already have an account?
                        <RouterLink
                            to="/login"
                            class="font-medium text-slate-950 underline decoration-slate-300 underline-offset-4 transition hover:decoration-slate-950"
                        >
                            Sign in here
                        </RouterLink>
                    </p>
                </div>
            </section>
        </div>
    </main>
</template>

<script setup lang="ts">
import RegisterForm from '@/components/auth/RegisterForm.vue'
</script>
