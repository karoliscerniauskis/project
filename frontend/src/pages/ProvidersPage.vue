<template>
    <div class="min-h-screen bg-primary-50">
        <TopNav />
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-semibold text-primary-900">Providers</h1>
                    <p class="text-primary-600 mt-1">Manage your organizations and vouchers</p>
                </div>
                <el-button type="primary" size="large" @click="navigateToCreate">
                    <el-icon class="mr-2"><Plus /></el-icon>
                    Create Provider
                </el-button>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-primary-100 overflow-hidden">
                <div class="p-6 border-b border-primary-100">
                    <el-input
                        v-model="searchQuery"
                        placeholder="Search providers by name..."
                        size="large"
                        clearable
                        @input="handleSearch"
                    >
                        <template #prefix>
                            <el-icon><Search /></el-icon>
                        </template>
                    </el-input>
                </div>

                <div v-if="loading && !providers.length" class="p-12 text-center">
                    <el-icon class="is-loading" :size="32">
                        <Loading />
                    </el-icon>
                    <p class="text-primary-500 mt-4">Loading providers...</p>
                </div>

                <div v-else-if="error" class="p-12 text-center">
                    <el-alert type="error" :title="error" :closable="false" show-icon />
                </div>

                <div v-else-if="!providers.length" class="p-12 text-center">
                    <p class="text-primary-500 text-lg">No providers found</p>
                    <p class="text-primary-400 text-sm mt-2">Create your first provider to get started</p>
                </div>

                <div v-else class="divide-y divide-primary-100">
                    <div
                        v-for="provider in providers"
                        :key="provider.id"
                        class="p-6 hover:bg-primary-50 transition-colors cursor-pointer"
                        @click="navigateToProvider(provider.id)"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-primary-900">{{ provider.name }}</h3>
                                <div class="flex items-center gap-3 mt-2">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-xs font-medium',
                                            provider.status === 'active'
                                                ? 'bg-accent-50 text-accent-700'
                                                : 'bg-primary-100 text-primary-600'
                                        ]"
                                    >
                                        {{ provider.status }}
                                    </span>
                                    <span
                                        v-if="provider.isAdmin"
                                        class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700"
                                    >
                                        Admin
                                    </span>
                                </div>
                            </div>
                            <el-icon class="text-primary-400"><ArrowRight /></el-icon>
                        </div>
                    </div>
                </div>

                <div v-if="pagination.totalPages > 1" class="p-6 border-t border-primary-100">
                    <el-pagination
                        v-model:current-page="currentPage"
                        :page-size="pagination.limit"
                        :total="pagination.total"
                        layout="prev, pager, next, total"
                        @current-change="handlePageChange"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Plus, Search, Loading, ArrowRight } from '@element-plus/icons-vue'
import { useProvider } from '@/composables/useProvider'
import TopNav from '@/components/layout/TopNav.vue'

const router = useRouter()
const { loading, error, providers, pagination, fetchProviders } = useProvider()

const currentPage = ref(1)
const searchQuery = ref('')
let searchTimeout: number | undefined

onMounted(() => {
    fetchProviders({ page: currentPage.value })
})

function handlePageChange(page: number) {
    currentPage.value = page
    fetchProviders({ page, name: searchQuery.value || undefined })
}

function handleSearch() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        currentPage.value = 1
        fetchProviders({ page: 1, name: searchQuery.value || undefined })
    }, 150)
}

function navigateToCreate() {
    router.push('/providers/create')
}

function navigateToProvider(id: string) {
    router.push(`/providers/${id}`)
}
</script>
