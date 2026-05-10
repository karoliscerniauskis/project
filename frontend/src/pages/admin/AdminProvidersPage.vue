<template>
    <div class="min-h-screen bg-primary-50">
        <TopNav />
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold text-primary-900">Admin - Providers</h1>
                <p class="text-primary-600 mt-1">Manage all providers in the system</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100 mb-6">
                <ProvidersFilter v-model="filters" @search="handleFilterChange" />
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
                <ProvidersTable
                    :providers="providers"
                    :pagination="pagination"
                    :loading="loading"
                    :error="error"
                    :show-admin-badge="false"
                    :clickable="false"
                    :show-actions="true"
                    empty-message="No providers found in the system"
                    @page-change="handlePageChange"
                    @approve="handleApprove"
                    @deactivate="handleDeactivate"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { providerApi } from '@/api/provider.api'
import type { Pagination, Provider } from '@/types'
import TopNav from '@/components/layout/TopNav.vue'
import ProvidersTable from '@/components/provider/ProvidersTable.vue'
import ProvidersFilter from '@/components/provider/ProvidersFilter.vue'
import { useConfirm } from '@/composables/useConfirm'
import { MESSAGES } from '@/constants/messages'

const providers = ref<Provider[]>([])
const pagination = ref<Pagination>({
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 0,
})
const loading = ref(false)
const error = ref<string | null>(null)

const filters = ref<{ name?: string; status?: string }>({})
const { confirm } = useConfirm()

onMounted(() => {
    fetchProviders()
})

async function fetchProviders(page = 1) {
    loading.value = true
    error.value = null

    try {
        const response = await providerApi.getAdminProviders({
            page,
            limit: pagination.value.limit,
            name: filters.value.name,
            status: filters.value.status,
        })
        providers.value = response.data
        pagination.value = response.pagination
    } catch (err) {
        error.value = err instanceof Error ? err.message : MESSAGES.ERROR.PROVIDER_LOAD
    } finally {
        loading.value = false
    }
}

function handlePageChange(page: number) {
    fetchProviders(page)
}

function handleFilterChange() {
    fetchProviders(1)
}

async function handleApprove(provider: Provider) {
    const confirmed = await confirm(
        MESSAGES.CONFIRM.PROVIDER_APPROVE(provider.name),
        'Approve Provider'
    )

    if (!confirmed) return

    try {
        await providerApi.approveProvider(provider.id)
        ElMessage.success(MESSAGES.SUCCESS.PROVIDER_APPROVED)
        await fetchProviders(pagination.value.page)
    } catch (err) {
        ElMessage.error(err instanceof Error ? err.message : MESSAGES.ERROR.PROVIDER_APPROVE)
    }
}

async function handleDeactivate(provider: Provider) {
    const confirmed = await confirm(
        MESSAGES.CONFIRM.PROVIDER_DEACTIVATE(provider.name),
        'Deactivate Provider'
    )

    if (!confirmed) return

    try {
        await providerApi.deactivateProvider(provider.id)
        ElMessage.success(MESSAGES.SUCCESS.PROVIDER_DEACTIVATED)
        await fetchProviders(pagination.value.page)
    } catch (err) {
        ElMessage.error(err instanceof Error ? err.message : MESSAGES.ERROR.PROVIDER_DEACTIVATE)
    }
}
</script>
