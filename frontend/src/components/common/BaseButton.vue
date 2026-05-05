<template>
    <component
        :is="to ? 'RouterLink' : 'button'"
        :to="to"
        :type="to ? undefined : type"
        :disabled="disabled || loading"
        :class="buttonClasses"
        @click="handleClick"
    >
        <span v-if="loading" class="inline-block animate-spin mr-2">⏳</span>
        <slot />
    </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type Variant = 'primary' | 'secondary' | 'danger' | 'success' | 'ghost'
type Size = 'sm' | 'md' | 'lg'

interface Props {
    variant?: Variant
    size?: Size
    disabled?: boolean
    loading?: boolean
    type?: 'button' | 'submit' | 'reset'
    to?: string
    fullWidth?: boolean
}

interface Emits {
    (e: 'click', event: Event): void
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
    disabled: false,
    loading: false,
    fullWidth: false,
})

const emit = defineEmits<Emits>()

const baseClasses =
    'inline-flex items-center justify-center font-semibold transition-all rounded-full focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed'

const variantClasses: Record<Variant, string> = {
    primary: 'bg-[#0071e3] text-white hover:bg-[#0077ed] active:bg-[#006edb]',
    secondary:
        'bg-transparent border-2 border-[#0071e3] text-[#0071e3] hover:bg-[#0071e3] hover:text-white',
    danger: 'bg-[#ff3b30] text-white hover:bg-[#ff453a] active:bg-[#ff2d20]',
    success: 'bg-[#34c759] text-white hover:bg-[#30d158] active:bg-[#28cd4c]',
    ghost: 'bg-[#f5f5f7] text-[#1d1d1f] hover:bg-[#e8e8ed]',
}

const sizeClasses: Record<Size, string> = {
    sm: 'px-4 py-1.5 text-xs min-h-[28px]',
    md: 'px-5 py-2.5 text-sm min-h-[44px]',
    lg: 'px-6 py-3 text-base min-h-[52px]',
}

const buttonClasses = computed(() => {
    return [
        baseClasses,
        variantClasses[props.variant],
        sizeClasses[props.size],
        props.fullWidth ? 'w-full' : '',
    ].join(' ')
})

function handleClick(event: Event) {
    if (!props.disabled && !props.loading) {
        emit('click', event)
    }
}
</script>
