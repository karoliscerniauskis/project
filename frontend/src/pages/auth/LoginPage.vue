<template>
    <LoginForm />
    <p>
        Don’t have an account?
        <RouterLink to="/register">Register here</RouterLink>
    </p>
</template>

<script setup lang="ts">
import LoginForm from '@/components/auth/LoginForm.vue'
</script>
