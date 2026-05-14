<template>
    <el-dialog v-model="dialogVisible" title="Change Provider" width="500">
        <div v-if="loadingLinkedProviders" class="flex justify-center py-8">
            <el-icon class="is-loading" :size="24">
                <Loading />
            </el-icon>
        </div>
        <div v-else-if="linkedProviders.length === 0" class="py-4">
            <el-alert
                type="info"
                :closable="false"
                show-icon
            >
                <template #title>
                    No linked providers available for this voucher
                </template>
            </el-alert>
        </div>
        <el-form v-else :model="form" label-position="top">
            <el-form-item label="New Provider" prop="newProviderId">
                <el-select v-model="form.newProviderId" placeholder="Select a provider" class="w-full">
                    <el-option
                        v-for="provider in linkedProviders"
                        :key="provider.id"
                        :label="provider.name"
                        :value="provider.id"
                        :disabled="provider.status !== 'active'"
                    />
                </el-select>
            </el-form-item>
        </el-form>
        <template #footer>
            <el-button @click="handleClose">Cancel</el-button>
            <el-button
                type="primary"
                :loading="loading"
                :disabled="!form.newProviderId"
                @click="handleSubmit"
            >
                Change Provider
            </el-button>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, type ModelRef } from 'vue'
import { ElMessage } from 'element-plus'
import { Loading } from '@element-plus/icons-vue'
import { voucherApi, type Voucher } from '@/api/voucher.api'
import { providerApi, type LinkedProvider } from '@/api/provider.api'

const props = defineProps<{
    voucher: Voucher
}>()

const emit = defineEmits<{
    'update:visible': [value: boolean]
    changed: []
}>()

const dialogVisible: ModelRef<boolean> = defineModel<boolean>('visible', { required: true })
const loading = ref(false)
const loadingLinkedProviders = ref(false)
const linkedProviders = ref<LinkedProvider[]>([])
const form = ref({
    newProviderId: '',
})

watch(dialogVisible, async (visible) => {
    if (visible) {
        form.value.newProviderId = ''
        linkedProviders.value = []
        await loadLinkedProviders()
    }
})

onMounted(async () => {
    if (dialogVisible.value) {
        await loadLinkedProviders()
    }
})

async function loadLinkedProviders() {
    loadingLinkedProviders.value = true
    try {
        linkedProviders.value = await providerApi.getLinkedProvidersForVoucher(props.voucher.id)
    } catch (err) {
        ElMessage.error(err instanceof Error ? err.message : 'Failed to load linked providers')
    } finally {
        loadingLinkedProviders.value = false
    }
}

async function handleSubmit() {
    if (!form.value.newProviderId) return

    loading.value = true

    try {
        await voucherApi.changeVoucherProvider(props.voucher.id, {
            newProviderId: form.value.newProviderId,
        })
        ElMessage.success('Voucher provider changed successfully')
        handleClose()
        emit('changed')
    } catch (err) {
        ElMessage.error(err instanceof Error ? err.message : 'Failed to change voucher provider')
    } finally {
        loading.value = false
    }
}

function handleClose() {
    dialogVisible.value = false
}
</script>
