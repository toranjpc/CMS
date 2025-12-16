<template>
  <div dir="rtl" lang="fa">

    <!-- لودینگ -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner"></div>
    </div>

    <AdminSidebar :collapsed="isCollapsed" @toggle="toggleSidebar" />

    <AdminNavbar :collapsed="isCollapsed" />

    <main class="main-content" :class="{ expanded: !isCollapsed }">
      <slot />
    </main>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

import AdminSidebar from '~/components/admin/Sidebar.vue'
import AdminNavbar from '~/components/admin/Navbar.vue'

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

useHead({
  titleTemplate: (title) =>
    title ? `${title} | پنل مدیریت` : 'پنل مدیریت'
})

// Define layout meta
definePageMeta({
  middleware: 'auth'
})
</script>
