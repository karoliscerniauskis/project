import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '@/pages/auth/LoginPage.vue'
import RegisterPage from '@/pages/auth/RegisterPage.vue'
import VerifyEmailPage from '@/pages/auth/VerifyEmailPage.vue'
import ProvidersPage from '@/pages/providers/ProvidersPage.vue'

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
    ],
})
