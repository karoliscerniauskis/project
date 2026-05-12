<template>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-primary-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-primary-900">Linked Providers</h2>
            <el-button :loading="loading" size="small" @click="loadLinkedProviders">
                <el-icon><Refresh /></el-icon>
            </el-button>
        </div>

        <div v-if="loading" class="flex justify-center py-8">
            <el-icon class="is-loading" :size="32">
                <Loading />
            </el-icon>
        </div>

        <div v-else-if="error" class="text-center py-8">
            <p class="text-red-600">{{ error }}</p>
        </div>

        <div v-else-if="!linkedProviders || linkedProviders.length === 0" class="text-center py-8">
            <p class="text-primary-600">No linked providers</p>
        </div>

        <div v-else class="space-y-2">
            <div
                v-for="provider in linkedProviders"
                :key="provider.id"
                class="flex items-center justify-between p-4 rounded-lg border border-primary-100 hover:bg-primary-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <el-icon :size="20" class="text-primary-600">
                        <Link />
                    </el-icon>
                    <div>
                        <p class="font-medium text-primary-900">{{ provider.name }}</p>
                        <p class="text-sm text-primary-600">{{ provider.status }}</p>
                    </div>
                </div>

                <el-button
                    type="danger"
                    size="small"
                    @click="handleUnlinkProvider(provider)"
                >
                    Unlink
                </el-button>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-primary-100">
            <h3 class="text-lg font-medium text-primary-900 mb-4">Link New Provider</h3>
            <div class="flex gap-3">
                <el-select
                    v-model="selectedProviderId"
                    placeholder="Select a provider to link"
                    class="flex-1"
                    filterable
                    :loading="loadingAvailableProviders"
                >
                    <el-option
                        v-for="provider in availableProviders"
                        :key="provider.id"
                        :label="provider.name"
                        :value="provider.id"
                    />
                </el-select>
                <el-button
                    type="primary"
                    :disabled="!selectedProviderId"
                    :loading="linking"
                    @click="handleLinkProvider"
                >
                    Link Provider
                </el-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Loading, Link, Refresh } from '@element-plus/icons-vue'
import { providerApi, type LinkedProvider } from '@/api/provider.api'
import { useAsyncState } from '@/composables/useAsyncState'
import { useConfirm } from '@/composables/useConfirm'
import { MESSAGES } from '@/constants/messages'

const props = defineProps<{
    providerId: string
}>()

const { data: linkedProviders, loading, error, execute: fetchLinkedProviders } = useAsyncState<LinkedProvider[]>()
const { data: availableProviders, loading: loadingAvailableProviders, execute: fetchAvailableProviders } = useAsyncState<LinkedProvider[]>()
const { confirm } = useConfirm()

const selectedProviderId = ref<string>('')
const linking = ref(false)

async function loadLinkedProviders() {
    await fetchLinkedProviders(() => providerApi.getLinkedProviders(props.providerId), {
        errorMessage: MESSAGES.ERROR.PROVIDER_LOAD,
    })
}

async function loadAvailableProviders() {
    await fetchAvailableProviders(() => providerApi.getAvailableProvidersToLink(props.providerId), {
        errorMessage: MESSAGES.ERROR.PROVIDER_LOAD,
    })
}

async function handleLinkProvider() {
    if (!selectedProviderId.value) return

    linking.value = true
    try {
        await providerApi.linkProvider(props.providerId, {
            linkedProviderId: selectedProviderId.value,
        })
        ElMessage.success('Provider linked successfully')
        selectedProviderId.value = ''
        await loadLinkedProviders()
    } catch (err) {
        ElMessage.error(err instanceof Error ? err.message : 'Failed to link provider')
    } finally {
        linking.value = false
    }
}

async function handleUnlinkProvider(provider: LinkedProvider) {
    const confirmed = await confirm(
        `Are you sure you want to unlink ${provider.name}?`,
        'Unlink Provider'
    )

    if (!confirmed) return

    try {
        await providerApi.unlinkProvider(props.providerId, provider.id)
        ElMessage.success('Provider unlinked successfully')
        await loadLinkedProviders()
    } catch (err) {
        ElMessage.error(err instanceof Error ? err.message : 'Failed to unlink provider')
    }
}

onMounted(() => {
    loadLinkedProviders()
    loadAvailableProviders()
})
</script>
