<template>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                            :class="column.headerClass"
                        >
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    <tr
                        v-for="(row, index) in data"
                        :key="getRowKey(row, index)"
                        class="hover:bg-slate-50 transition-colors"
                        :class="rowClass"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-4 whitespace-nowrap"
                            :class="column.cellClass"
                        >
                            <slot
                                :name="`cell-${column.key}`"
                                :row="row"
                                :column="column"
                                :value="getCellValue(row, column.key)"
                            >
                                {{ getCellValue(row, column.key) }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts" generic="T extends Record<string, unknown>">
interface Column {
    key: string
    label: string
    headerClass?: string
    cellClass?: string
}

interface Props {
    columns: Column[]
    data: T[]
    rowKey?: string | ((row: T) => string | number)
    rowClass?: string | ((row: T) => string)
}

const props = withDefaults(defineProps<Props>(), {
    rowKey: 'id',
    rowClass: '',
})

function getRowKey(row: T, index: number): string | number {
    if (typeof props.rowKey === 'function') {
        return props.rowKey(row)
    }
    return row[props.rowKey] ?? index
}

function getCellValue(row: T, key: string): unknown {
    const keys = key.split('.')
    let value: unknown = row

    for (const k of keys) {
        value = (value as Record<string, unknown>)?.[k]
        if (value === undefined) break
    }

    return value
}
</script>
