<template>

</template>

<script setup>
definePageMeta({
    layout: 'dashboard',
    middleware: 'auth',
    title: 'داشبورد مدیریت'
})
const { $api } = useNuxtApp()

const config = useRuntimeConfig()
const baseUrl = config.public.apiBase
const loading = ref(false)
const error = ref(null)




</script>