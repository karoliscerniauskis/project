import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '@/pages/auth/LoginPage.vue'
import RegisterPage from '@/pages/auth/RegisterPage.vue'
import VerifyEmailPage from '@/pages/auth/VerifyEmailPage.vue'
import LogoutPage from '@/pages/auth/LogoutPage.vue'
import ProvidersPage from '@/pages/providers/ProvidersPage.vue'
import CreateProviderPage from '@/pages/providers/CreateProviderPage.vue'

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: LoginPage,
        },
        {
            path: '/register',
            name: 'register',
            component: RegisterPage,
        },
        {
            path: '/verify-email/:slug',
            name: 'verify-email',
            component: VerifyEmailPage,
            props: true,
        },
        {
            path: '/providers',
            name: 'providers',
            component: ProvidersPage,
        },
        {
            path: '/logout',
            name: 'logout',
            component: LogoutPage,
        },
        {
            path: '/provider/create',
            name: 'provider-create',
            component: CreateProviderPage,
        },
    ],
})
