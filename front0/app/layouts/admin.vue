<template>
  <div class="admin-panel">
    <Sidebar :collapsed="sidebarCollapsed" :active-url="activeUrl" @toggle-submenu="toggleSubmenu" />
    <div class="main-area" :class="{ expanded: !sidebarCollapsed }">
      <Navbar @toggle-menu="toggleSidebar" />
      <div id="mainContent">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
// ❌ حذف این خط: import { useRoute } from 'vue-router'
// ✅ در Nuxt 3، useRoute به صورت auto-import است
import Sidebar from '~/components/admin/Sidebar.vue'
import Navbar from '~/components/admin/Navbar.vue'

const route = useRoute()

let touchStartX = 0
let touchEndX = 0
const swipeThreshold = 50

// reactive state
const sidebarCollapsed = ref(false)
const activeUrl = ref('')

// استفاده از route برای گرفتن URL جاری
onMounted(() => {
  activeUrl.value = route.path
  
  // اضافه کردن event listener ها فقط در سمت کلاینت
  if (process.client) {
    document.addEventListener('touchstart', handleTouchStart)
    document.addEventListener('touchmove', handleTouchMove)
    document.addEventListener('touchend', handleTouchEnd)
  }
})

// watch برای تغییر route
watch(() => route.path, (newPath) => {
  activeUrl.value = newPath
})

onUnmounted(() => {
  // پاک کردن event listener ها
  if (process.client) {
    document.removeEventListener('touchstart', handleTouchStart)
    document.removeEventListener('touchmove', handleTouchMove)
    document.removeEventListener('touchend', handleTouchEnd)
  }
})

function handleTouchStart(e: TouchEvent) {
  touchStartX = e.touches[0].clientX
}

function handleTouchMove(e: TouchEvent) {
  touchEndX = e.touches[0].clientX
}

function handleTouchEnd() {
  const distance = touchEndX - touchStartX
  if (Math.abs(distance) > swipeThreshold) {
    if (distance < 0) {
      // swipe left: باز کردن sidebar
      sidebarCollapsed.value = false
    } else {
      // swipe right: بستن sidebar
      sidebarCollapsed.value = true
    }
  }
}

// toggle sidebar
function toggleSidebar() {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

// زیرمنوها در Sidebar کامپوننت مدیریت می‌شوند
// این تابع فقط برای دریافت event از Sidebar است
function toggleSubmenu(index: number) {
  // منطق در Sidebar کامپوننت مدیریت می‌شود
  // این تابع می‌تواند برای لاگ یا عملیات اضافی استفاده شود
}
</script>

<style>
.admin-panel {
  display: flex;
}

.main-area {
  flex: 1;
  transition: margin-left 0.3s ease;
  width: 100%;
}

.main-area.expanded {
  margin-left: 250px;
}
</style>
