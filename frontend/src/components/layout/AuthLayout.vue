<template>
    <div class="min-h-screen flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-[440px]">
            <div class="bg-white rounded-[20px] shadow-xl border border-primary-100/50 backdrop-blur-sm">
                <div class="px-10 pt-12 pb-8">
                    <h1 class="text-[32px] font-semibold text-primary-900 text-center tracking-tight leading-tight">
                        {{ title }}
                    </h1>
                    <p v-if="subtitle" class="text-[15px] text-primary-500 text-center mt-3 leading-relaxed">
                        {{ subtitle }}
                    </p>
                </div>

                <div class="px-10 pb-10">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="px-10 pb-10 pt-2">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string
        subtitle?: string
    }>(),
    {
        subtitle: '',
    }
)
</script>

