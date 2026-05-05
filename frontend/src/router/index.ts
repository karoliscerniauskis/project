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
import ValidateVoucherPage from '@/pages/providers/ValidateVoucherPage.vue'
import ClaimVoucherPage from '@/pages/vouchers/ClaimVoucherPage.vue'
import TransferVoucherPage from '@/pages/vouchers/TransferVoucherPage.vue'
import NotificationsPage from '@/pages/notifications/NotificationsPage.vue'
import AcceptProviderInvitationPage from '@/pages/providers/AcceptProviderInvitationPage.vue'
import AdminProvidersPage from '@/pages/admin/AdminProvidersPage.vue'

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
        {
            path: '/providers/:id/vouchers/validate',
            name: 'provider-voucher-validate',
            component: ValidateVoucherPage,
            props: true,
        },
        {
            path: '/vouchers/:voucherId/claim',
            name: 'voucher-claim',
            component: ClaimVoucherPage,
            props: true,
        },
        {
            path: '/vouchers/:voucherId/transfer',
            name: 'voucher-transfer',
            component: TransferVoucherPage,
            props: true,
        },
        {
            path: '/me/notifications',
            name: 'my-notifications',
            component: NotificationsPage,
        },
        {
            path: '/provider-invitations/:slug/accept',
            name: 'provider-invitation-accept',
            component: AcceptProviderInvitationPage,
            props: true,
        },
        {
            path: '/admin/providers',
            name: 'admin-providers',
            component: AdminProvidersPage,
        },
    ],
})
