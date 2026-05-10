<template>
    <div>
        <div v-if="loading" class="flex justify-center py-12">
            <el-icon class="is-loading" :size="32">
                <Loading />
            </el-icon>
        </div>

        <div v-else-if="error" class="py-8">
            <el-alert type="error" :title="error" :closable="false" show-icon />
        </div>

        <div v-else-if="!providers.length" class="p-12 text-center">
            <p class="text-primary-500 text-lg">No providers found</p>
            <p class="text-primary-400 text-sm mt-2">
                {{ emptyMessage || 'No providers match your search criteria' }}
            </p>
        </div>

        <div v-else>
            <el-table
                :data="providers"
                stripe
                style="width: 100%"
                @row-click="clickable ? handleRowClick : undefined"
                :class="{ 'cursor-pointer': clickable }"
            >
                <el-table-column prop="name" label="Name" min-width="200">
                    <template #default="{ row }">
                        <span class="font-medium text-primary-900">{{ row.name }}</span>
                    </template>
                </el-table-column>

                <el-table-column prop="status" label="Status" width="150">
                    <template #default="{ row }">
                        <el-tag
                            :type="getProviderStatusType(row.status)"
                            effect="light"
                            size="small"
                        >
                            {{ row.status }}
                        </el-tag>
                    </template>
                </el-table-column>

                <el-table-column v-if="showAdminBadge" prop="isAdmin" label="Role" width="120">
                    <template #default="{ row }">
                        <el-tag v-if="row.isAdmin" type="info" effect="light" size="small">
                            Admin
                        </el-tag>
                        <span v-else class="text-primary-400">User</span>
                    </template>
                </el-table-column>

                <el-table-column v-if="showActions" label="Actions" width="200" align="right">
                    <template #default="{ row }">
                        <div class="flex gap-2 justify-end" @click.stop>
                            <el-button
                                v-if="row.status === 'pending'"
                                type="success"
                                size="small"
                                @click="handleApprove(row)"
                            >
                                Approve
                            </el-button>
                            <el-button
                                v-if="row.status === 'active'"
                                type="warning"
                                size="small"
                                @click="handleDeactivate(row)"
                            >
                                Deactivate
                            </el-button>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column v-if="clickable" width="50" align="right">
                    <template #default>
                        <el-icon class="text-primary-400">
                            <ArrowRight />
                        </el-icon>
                    </template>
                </el-table-column>
            </el-table>

            <div v-if="pagination.totalPages > 1" class="mt-6 flex justify-center">
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
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Loading, ArrowRight } from '@element-plus/icons-vue'
import type { Provider, Pagination } from '@/types'
import { getProviderStatusType } from '@/utils/status'

interface Props {
    providers: Provider[]
    pagination: Pagination
    loading?: boolean
    error?: string | null
    showAdminBadge?: boolean
    clickable?: boolean
    showActions?: boolean
    emptyMessage?: string
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    error: null,
    showAdminBadge: true,
    clickable: true,
    showActions: false,
    emptyMessage: '',
})

const emit = defineEmits<{
    pageChange: [page: number]
    rowClick: [provider: Provider]
    approve: [provider: Provider]
    deactivate: [provider: Provider]
}>()

const currentPage = ref(props.pagination.page)

watch(
    () => props.pagination.page,
    (newPage) => {
        currentPage.value = newPage
    }
)

function handlePageChange(page: number) {
    emit('pageChange', page)
}

function handleRowClick(row: Provider) {
    emit('rowClick', row)
}

function handleApprove(provider: Provider) {
    emit('approve', provider)
}

function handleDeactivate(provider: Provider) {
    emit('deactivate', provider)
}

</script>
