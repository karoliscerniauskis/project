<template>
    <header>
        <nav>
            <RouterLink to="/providers">Providers</RouterLink>
            <RouterLink to="/me/vouchers">My vouchers</RouterLink>
            <RouterLink to="/login">Login</RouterLink>
            <RouterLink to="/register">Register</RouterLink>
            <RouterLink to="/logout">Logout</RouterLink>
        </nav>
    </header>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>
