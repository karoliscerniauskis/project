<template>
    <header class="border-b border-white/40 bg-white/60 backdrop-blur-xl">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6 sm:px-8 lg:px-12">
            <div class="flex items-center gap-8">
                <RouterLink to="/providers" class="text-lg font-semibold text-slate-950">
                    Voucher Platform
                </RouterLink>

                <div class="hidden gap-6 md:flex">
                    <RouterLink
                        to="/providers"
                        class="text-sm font-medium text-slate-600 transition hover:text-slate-950"
                        active-class="!text-slate-950"
                    >
                        Providers
                    </RouterLink>
                    <RouterLink
                        to="/me/vouchers"
                        class="text-sm font-medium text-slate-600 transition hover:text-slate-950"
                        active-class="!text-slate-950"
                    >
                        My vouchers
                    </RouterLink>
                    <RouterLink
                        to="/me/notifications"
                        class="text-sm font-medium text-slate-600 transition hover:text-slate-950"
                        active-class="!text-slate-950"
                    >
                        Notifications
                    </RouterLink>
                    <RouterLink
                        v-if="isAdminUser"
                        to="/admin/providers"
                        class="text-sm font-medium text-slate-600 transition hover:text-slate-950"
                        active-class="!text-slate-950"
                    >
                        Admin
                    </RouterLink>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span
                    v-if="isAuthenticated && username !== null"
                    class="hidden max-w-56 truncate rounded-xl bg-slate-100 px-3 py-2 text-sm font-medium text-slate-600 sm:inline"
                    :title="username"
                >
                    {{ username }}
                </span>
                <RouterLink
                    v-if="!isAuthenticated"
                    to="/login"
                    class="text-sm font-medium text-slate-600 transition hover:text-slate-950"
                >
                    Login
                </RouterLink>
                <RouterLink
                    v-if="!isAuthenticated"
                    to="/register"
                    class="inline-flex h-9 items-center justify-center rounded-xl bg-slate-950 px-4 text-sm font-medium !text-white transition hover:bg-slate-800"
                >
                    Register
                </RouterLink>
                <RouterLink
                    v-if="isAuthenticated"
                    to="/logout"
                    class="text-sm font-medium text-slate-600 transition hover:text-slate-950"
                >
                    Logout
                </RouterLink>
            </div>
        </nav>
    </header>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { getUsername, isAdmin } from '@/utils/auth'

const isAuthenticated = computed(() => {
    return localStorage.getItem('token') !== null
})

const isAdminUser = computed(() => {
    return isAdmin()
})

const username = computed(() => {
    return getUsername()
})
</script>
