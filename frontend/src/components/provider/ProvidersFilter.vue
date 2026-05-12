<template>
    <div class="bg-white rounded-2xl shadow-sm border border-primary-100 p-6 mb-6">
        <el-row :gutter="16">
            <el-col :xs="24" :sm="24" :md="12" :lg="12">
                <el-input
                    v-model="searchValue"
                    placeholder="Search providers by name..."
                    size="large"
                    clearable
                    @input="handleSearch"
                >
                    <template #prefix>
                        <el-icon><Search /></el-icon>
                    </template>
                </el-input>
            </el-col>

            <el-col :xs="24" :sm="24" :md="12" :lg="12" class="mt-4 md:mt-0">
                <el-select
                    v-model="statusValue"
                    placeholder="Filter by status"
                    size="large"
                    clearable
                    class="w-full"
                    @change="handleStatusChange"
                >
                    <el-option label="Active" value="active" />
                    <el-option label="Pending" value="pending" />
                    <el-option label="Deactivated" value="deactivated" />
                </el-select>
            </el-col>
        </el-row>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Search } from '@element-plus/icons-vue'

interface Props {
    modelValue?: {
        name?: string
        status?: string
    }
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => ({}),
})

const emit = defineEmits<{
    'update:modelValue': [value: { name?: string; status?: string }]
    search: []
}>()

const searchValue = ref(props.modelValue.name || '')
const statusValue = ref(props.modelValue.status || '')

let searchTimeout: number | undefined

function handleSearch() {
    clearTimeout(searchTimeout)
    searchTimeout = window.setTimeout(() => {
        emit('update:modelValue', {
            name: searchValue.value || undefined,
            status: statusValue.value || undefined,
        })
        emit('search')
    }, 300)
}

function handleStatusChange() {
    emit('update:modelValue', {
        name: searchValue.value || undefined,
        status: statusValue.value || undefined,
    })
    emit('search')
}
</script>
