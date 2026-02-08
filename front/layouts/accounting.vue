<template>
  <div dir="rtl" lang="fa">

    <!-- لودینگ -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner"></div>
    </div>

    <AccountingSidebar :collapsed="isCollapsed" @toggle="toggleSidebar" />

    <AccountingNavbar :collapsed="isCollapsed" />

    <main class="main-content" :class="{ expanded: !isCollapsed }">
      <slot />
    </main>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '~/components/useAuth'

import AccountingSidebar from '~/components/accounting/Sidebar.vue'
import AccountingNavbar from '~/components/accounting/Navbar.vue'

const authStore = useAuth()
const isCollapsed = ref(false)
const loading = ref(false)

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

const router = useRouter()

router.beforeEach(() => {
  loading.value = true
})

router.afterEach(() => {
  loading.value = false
})

const route = useRoute()

useHead({
  title: computed(() => {
    const pageTitle = route.meta?.title
    return pageTitle ? `سیستم حسابداری | ${pageTitle}` : 'سیستم حسابداری'
  })
})

// Define layout meta
definePageMeta({
  middleware: 'auth'
})
</script>
