import { ref } from 'vue'
import { providerApi } from '@/api/provider.api'
import type { CreateProviderRequest, Provider, Pagination, GetProvidersParams } from '@/types'

export function useProvider() {
    const loading = ref(false)
    const error = ref<string | null>(null)
    const success = ref<string | null>(null)
    const providers = ref<Provider[]>([])
    const pagination = ref<Pagination>({
        page: 1,
        limit: 10,
        total: 0,
        totalPages: 0,
    })

    async function createProvider(request: CreateProviderRequest) {
        loading.value = true
        error.value = null
        success.value = null

        try {
            await providerApi.createProvider(request)
            success.value = 'Provider created successfully!'
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to create provider'
        } finally {
            loading.value = false
        }
    }

    async function fetchProviders(params?: GetProvidersParams) {
        loading.value = true
        error.value = null

        try {
            const response = await providerApi.getProviders(params)
            providers.value = response.data
            pagination.value = response.pagination
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Failed to fetch providers'
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        error,
        success,
        providers,
        pagination,
        createProvider,
        fetchProviders,
    }
}
