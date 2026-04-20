import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '@/pages/auth/LoginPage.vue'
import RegisterPage from '@/pages/auth/RegisterPage.vue'
import VerifyEmailPage from '@/pages/auth/VerifyEmailPage.vue'
import LogoutPage from '@/pages/auth/LogoutPage.vue'
import ProvidersPage from '@/pages/providers/ProvidersPage.vue'
import CreateProviderPage from '@/pages/providers/CreateProviderPage.vue'
import ProviderPage from '@/pages/providers/ProviderPage.vue'
import InviteProviderUserPage from '@/pages/providers/InviteProviderUserPage.vue'
import CreateVoucherPage from '@/pages/providers/CreateVoucherPage.vue'
import ProviderVouchersPage from '@/pages/providers/ProviderVouchersPage.vue'
import MyVouchersPage from '@/pages/vouchers/MyVouchersPage.vue'

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
        {
            path: '/providers/:id',
            name: 'provider',
            component: ProviderPage,
            props: true,
        },
        {
            path: '/providers/:id/invite',
            name: 'provider-invite-user',
            component: InviteProviderUserPage,
            props: true,
        },
        {
            path: '/providers/:id/vouchers/create',
            name: 'provider-voucher-create',
            component: CreateVoucherPage,
            props: true,
        },
        {
            path: '/providers/:id/vouchers',
            name: 'provider-vouchers',
            component: ProviderVouchersPage,
            props: true,
        },
        {
            path: '/me/vouchers',
            name: 'my-vouchers',
            component: MyVouchersPage,
        },
    ],
})
