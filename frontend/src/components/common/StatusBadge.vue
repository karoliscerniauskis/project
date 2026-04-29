<template>
    <span
        :class="[baseClasses, statusClasses]"
        class="px-3 py-1 text-xs font-semibold rounded-full inline-flex items-center gap-1"
    >
        <slot name="icon" />
        <span>{{ label }}</span>
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { getVoucherStatusClasses, formatVoucherStatus, type VoucherStatus } from '@/utils/status'

interface Props {
    status: VoucherStatus | string
    label?: string
    variant?: 'voucher' | 'custom'
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'voucher',
})

const baseClasses = 'px-3 py-1 text-xs font-semibold rounded-full'

const statusClasses = computed(() => {
    if (props.variant === 'voucher') {
        return getVoucherStatusClasses(props.status as VoucherStatus)
    }
    return ''
})

const label = computed(() => {
    if (props.label) {
        return props.label
    }
    if (props.variant === 'voucher') {
        return formatVoucherStatus(props.status as VoucherStatus)
    }
    return props.status
})
</script>
