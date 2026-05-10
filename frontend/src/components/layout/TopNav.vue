<template>
    <nav class="bg-white border-b border-primary-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4 sm:gap-8">
                    <router-link to="/providers" class="text-lg sm:text-xl font-semibold text-primary-900">
                        VoucherApp
                    </router-link>

                    <div v-if="!isMobile" class="flex items-center gap-1">
                        <router-link
                            to="/vouchers"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-primary-700 hover:bg-primary-50 hover:text-accent-600 transition-colors"
                            active-class="bg-accent-50 text-accent-700"
                        >
                            My Vouchers
                        </router-link>
                        <router-link
                            to="/providers"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-primary-700 hover:bg-primary-50 hover:text-accent-600 transition-colors"
                            active-class="bg-accent-50 text-accent-700"
                        >
                            My Providers
                        </router-link>
                        <router-link
                            v-if="isAdmin"
                            to="/admin/providers"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-primary-700 hover:bg-primary-50 hover:text-accent-600 transition-colors"
                            active-class="bg-accent-50 text-accent-700"
                        >
                            Admin
                        </router-link>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    <el-badge :value="notificationCount" :hidden="notificationCount === 0">
                        <el-button circle @click="navigateToNotifications">
                            <el-icon :size="20"><Bell /></el-icon>
                        </el-button>
                    </el-badge>

                    <el-dropdown @command="handleCommand">
                        <div class="flex items-center gap-2 px-2 sm:px-3 py-2 rounded-lg hover:bg-primary-50 cursor-pointer transition-colors">
                            <el-avatar :size="32" class="bg-accent-600">
                                <el-icon><User /></el-icon>
                            </el-avatar>
                            <span class="hidden sm:block text-sm font-medium text-primary-900">{{ userEmail }}</span>
                            <el-icon class="hidden sm:block text-primary-400"><ArrowDown /></el-icon>
                        </div>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item command="profile">
                                    <el-icon><User /></el-icon>
                                    Edit Profile
                                </el-dropdown-item>
                                <el-dropdown-item command="logout" divided>
                                    <el-icon><SwitchButton /></el-icon>
                                    Logout
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>

                    <el-button v-if="isMobile" text @click="mobileMenuOpen = !mobileMenuOpen">
                        <el-icon :size="24"><Menu /></el-icon>
                    </el-button>
                </div>
            </div>

            <div v-if="mobileMenuOpen && isMobile" class="border-t border-primary-100 py-3">
                <router-link
                    to="/vouchers"
                    class="block px-4 py-2 rounded-lg text-sm font-medium text-primary-700 hover:bg-primary-50 hover:text-accent-600 transition-colors"
                    active-class="bg-accent-50 text-accent-700"
                    @click="mobileMenuOpen = false"
                >
                    My Vouchers
                </router-link>
                <router-link
                    to="/providers"
                    class="block px-4 py-2 rounded-lg text-sm font-medium text-primary-700 hover:bg-primary-50 hover:text-accent-600 transition-colors"
                    active-class="bg-accent-50 text-accent-700"
                    @click="mobileMenuOpen = false"
                >
                    My Providers
                </router-link>
                <router-link
                    v-if="isAdmin"
                    to="/admin/providers"
                    class="block px-4 py-2 rounded-lg text-sm font-medium text-primary-700 hover:bg-primary-50 hover:text-accent-600 transition-colors"
                    active-class="bg-accent-50 text-accent-700"
                    @click="mobileMenuOpen = false"
                >
                    Admin
                </router-link>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Bell, User, ArrowDown, SwitchButton, Menu } from '@element-plus/icons-vue'
import { useAuth } from '@/composables/useAuth'
import { useUser } from '@/composables/useUser'
import { useWindowSize } from '@vueuse/core'
import { notificationApi } from '@/api/notification.api'

const router = useRouter()
const { logout } = useAuth()
const { currentUser, fetchCurrentUser } = useUser()
const { width } = useWindowSize()

const notificationCount = ref(0)
const mobileMenuOpen = ref(false)

const userEmail = computed(() => currentUser.value?.email || 'Loading...')
const isMobile = computed(() => width.value < 768)
const isAdmin = computed(() => currentUser.value?.roles?.includes('ROLE_ADMIN') ?? false)

async function fetchNotificationCount() {
    try {
        notificationCount.value = await notificationApi.getUnreadCount()
    } catch (err) {
        console.error('Failed to fetch notification count', err)
    }
}

onMounted(() => {
    fetchCurrentUser()
    fetchNotificationCount()
    // Poll for new notifications every 30 seconds
    setInterval(fetchNotificationCount, 30000)
})

function handleCommand(command: string) {
    if (command === 'profile') {
        router.push('/profile')
    } else if (command === 'logout') {
        logout()
    }
}

function navigateToNotifications() {
    router.push('/notifications')
}
</script>
