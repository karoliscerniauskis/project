import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { storage } from '@/utils/storage'
import { userApi } from '@/api/user.api'

const routes: RouteRecordRaw[] = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/auth/LoginPage.vue'),
        meta: { requiresGuest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/pages/auth/RegisterPage.vue'),
        meta: { requiresGuest: true },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('@/pages/auth/ForgotPasswordPage.vue'),
        meta: { requiresGuest: true },
    },
    {
        path: '/reset-password/:token',
        name: 'reset-password',
        component: () => import('@/pages/auth/ResetPasswordPage.vue'),
        meta: { requiresGuest: true },
    },
    {
        path: '/verify-email/:token',
        name: 'verify-email',
        component: () => import('@/pages/auth/VerifyEmailPage.vue'),
        meta: { requiresGuest: true },
    },
    {
        path: '/providers',
        name: 'providers',
        component: () => import('@/pages/ProvidersPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/providers/create',
        name: 'create-provider',
        component: () => import('@/pages/provider/CreateProviderPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/providers/:id',
        name: 'provider-details',
        component: () => import('@/pages/provider/ProviderDetailsPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/provider-invitations/:slug/accept',
        name: 'accept-provider-invitation',
        component: () => import('@/pages/provider/AcceptProviderInvitationPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => import('@/pages/profile/ProfilePage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/providers',
        name: 'admin-providers',
        component: () => import('@/pages/admin/AdminProvidersPage.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
        path: '/vouchers',
        name: 'vouchers',
        component: () => import('@/pages/voucher/VouchersPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/notifications',
        name: 'notifications',
        component: () => import('@/pages/notification/NotificationsPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/',
        redirect: '/login',
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to) => {
    const isAuthenticated = storage.hasTokens()

    if (to.meta.requiresAuth && !isAuthenticated) {
        return '/login'
    }

    if (to.meta.requiresGuest && isAuthenticated) {
        return '/providers'
    }

    if (to.meta.requiresAdmin) {
        try {
            const user = await userApi.getCurrentUser()
            if (!user.roles.includes('ROLE_ADMIN')) {
                return '/providers'
            }
        } catch {
            return '/login'
        }
    }

    return true
})

export default router
