<template>
    <div class="min-h-screen bg-primary-50">
        <TopNav />
        <div class="max-w-4xl mx-auto px-6 py-8">
            <div v-if="loading" class="flex justify-center items-center py-12">
                <el-icon class="is-loading" :size="32">
                    <Loading />
                </el-icon>
            </div>

            <div v-else-if="provider" class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-primary-900">{{ provider.name }}</h1>
                    </div>
                    <el-button @click="navigateBack">
                        <el-icon class="mr-2"><ArrowLeft /></el-icon>
                        Back to Providers
                    </el-button>
                </div>
                <UseVoucherForm :provider-id="provider.id" @used="handleVoucherUsed" />
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <CreateVoucherForm :provider-id="provider.id" @created="handleVoucherCreated" />
                    <ValidateVoucherForm :provider-id="provider.id" />
                </div>
                <ProviderVouchersList ref="vouchersListRef" :provider-id="provider.id" />
                <ProviderUsersList v-if="provider.isAdmin" :provider-id="provider.id" :is-admin="provider.isAdmin" />
                <ProviderInvitationsList
                    v-if="provider.isAdmin"
                    ref="invitationsListRef"
                    :provider-id="provider.id"
                    :is-admin="provider.isAdmin"
                />
                <InviteUserForm
                    v-if="provider.isAdmin"
                    :provider-id="provider.id"
                    @invited="handleInvited"
                />
                <ProviderReminderSettings v-if="provider.isAdmin" :provider-id="provider.id" />
                <LinkedProviders
                    v-if="provider.isAdmin"
                    ref="linkedProvidersRef"
                    :provider-id="provider.id"
                />
            </div>

            <div v-else-if="error" class="text-center py-12">
                <p class="text-red-600">{{ error }}</p>
                <el-button type="primary" class="mt-4" @click="navigateBack">
                    Back to Providers
                </el-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Loading, ArrowLeft } from '@element-plus/icons-vue'
import { providerApi } from '@/api/provider.api'
import type { Provider } from '@/types'
import TopNav from '@/components/layout/TopNav.vue'
import ProviderUsersList from '@/components/provider/ProviderUsersList.vue'
import ProviderInvitationsList from '@/components/provider/ProviderInvitationsList.vue'
import InviteUserForm from '@/components/provider/InviteUserForm.vue'
import CreateVoucherForm from '@/components/voucher/CreateVoucherForm.vue'
import ValidateVoucherForm from '@/components/voucher/ValidateVoucherForm.vue'
import UseVoucherForm from '@/components/voucher/UseVoucherForm.vue'
import ProviderVouchersList from '@/components/voucher/ProviderVouchersList.vue'
import ProviderReminderSettings from '@/components/provider/ProviderReminderSettings.vue'
import LinkedProviders from '@/components/provider/LinkedProviders.vue'
import { useAsyncState } from '@/composables/useAsyncState'
import { MESSAGES } from '@/constants/messages'

const router = useRouter()
const route = useRoute()

const { data: provider, loading, error, execute: fetchProvider } = useAsyncState<Provider>()
const invitationsListRef = ref<InstanceType<typeof ProviderInvitationsList> | null>(null)
const vouchersListRef = ref<InstanceType<typeof ProviderVouchersList> | null>(null)

async function loadProvider() {
    const providerId = route.params.id as string
    await fetchProvider(() => providerApi.getProvider(providerId), {
        errorMessage: MESSAGES.ERROR.PROVIDER_LOAD,
    })
}

function navigateBack() {
    router.push('/providers')
}

function handleInvited() {
    invitationsListRef.value?.fetchInvitations()
}

function handleVoucherCreated() {
    vouchersListRef.value?.fetchVouchers()
}

function handleVoucherUsed() {
    vouchersListRef.value?.fetchVouchers()
}

onMounted(() => {
    loadProvider()
})
</script>
