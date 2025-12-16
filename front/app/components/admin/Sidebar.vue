<template>
  <aside :class="['sidebar', { collapsed }]">
    <div class="sidebar-header d-flex align-items-center">
      <button
        class="toggle-btn d-none d-md-block me-2"
        @click="$emit('toggle')"
      >
        <i class="fas fa-bars"></i>
      </button>
    </div>

    <ul class="sidebar-menu">
      <!-- داشبورد -->
      <li>
        <NuxtLink
          to="/dashboard"
          class="baseUrl"
          :class="{ active: isActive('/dashboard') }"
        >
          <i class="fas fa-home"></i>
          <span class="link-text">داشبورد</span>
        </NuxtLink>
      </li>

      <!-- کاربران (آکاردئونی) -->
      <li :class="{ open: isUsersOpen }">
        <a href="javascript:void(0)" @click="toggleUsers">
          <i class="fas fa-users"></i>
          <span class="link-text">کاربران</span>
          <!-- <i class="fas fa-chevron-down arrow"></i> -->
        </a>

        <ul class="submenu" v-show="isUsersOpen">
          <li>
            <NuxtLink
              to="/dashboard/users/create"
              :class="{ active: isActive('/dashboard/users/create') }"
            >
              افزودن کاربر
            </NuxtLink>
          </li>
          <li>
            <NuxtLink
              to="/dashboard/users"
              :class="{ active: isActive('/dashboard/users') }"
            >
              لیست کاربران
            </NuxtLink>
          </li>
          <li>
            <NuxtLink
              to="/dashboard/user-categories"
              :class="{ active: isActive('/dashboard/user-categories') }"
            >
              دسته‌بندی کاربران
            </NuxtLink>
          </li>
          <li>
            <NuxtLink
              to="/dashboard/user-roles"
              :class="{ active: isActive('/dashboard/user-roles') }"
            >
              نقش‌های کاربری
            </NuxtLink>
          </li>
          <li>
            <NuxtLink
              to="/dashboard/user-plans"
              :class="{ active: isActive('/dashboard/user-plans') }"
            >
              پل‌های کاربری
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- آمار -->
      <li>
        <NuxtLink
          to="/dashboard/stats"
          class="baseUrl"
          :class="{ active: isActive('/dashboard/stats') }"
        >
          <i class="fas fa-chart-line"></i>
          <span class="link-text">آمار</span>
        </NuxtLink>
      </li>
    </ul>
  </aside>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
})

const route = useRoute()

/* وضعیت باز بودن منوی کاربران */
const isUsersOpen = ref(false)

/* تشخیص active */
const isActive = (path) => {
  if (path === '/dashboard') return route.path === '/dashboard'
  return route.path.startsWith(path)
}

/* باز/بسته کردن دستی */
const toggleUsers = () => {
  isUsersOpen.value = !isUsersOpen.value
}

/* باز شدن خودکار بر اساس route */
watch(
  () => route.path,
  (path) => {
    isUsersOpen.value = path.startsWith('/dashboard/user')
  },
  { immediate: true }
)
</script>
