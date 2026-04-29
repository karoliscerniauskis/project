<template>
    <TopNav v-if="!isAuthPage" />
    <router-view />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import TopNav from '@/components/layout/TopNav.vue'

const route = useRoute()
const isAuthPage = computed(() => {
    return route.path === '/login' || route.path === '/register'
})
</script>
