<template>
  <aside :class="['sidebar', { collapsed }]">
    <div class="sidebar-header d-flex align-items-center">
      <button class="toggle-btn d-none d-md-block me-2" @click="$emit('toggle')">
        <i class="fa fa-bars"></i>
      </button>
    </div>

    <ul class="sidebar-menu">
      <!-- داشبورد -->
      <li>
        <NuxtLink to="/dashboard" class="baseUrl" :class="{ active: isActive('/dashboard') }">
          <i class="fa fa-home"></i>
          <span class="link-text">داشبورد</span>
        </NuxtLink>
      </li>

      <!-- کاربران (آکاردئونی) -->
      <li :class="{ open: isUsersOpen }">
        <a href="javascript:void(0)" @click="toggleUsers">
          <i class="fa fa-users"></i>
          <span class="link-text">کاربران</span>
          <!-- <i class="fa fa-chevron-down arrow"></i> -->
        </a>

        <ul class="submenu" v-show="isUsersOpen">
          <li>
            <NuxtLink to="/dashboard/users" :class="{ active: isActive('/dashboard/users') }">
              لیست کاربران
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/users/categories" :class="{ active: isActive('/dashboard/users/categories') }">
              دسته‌بندی کاربران
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/users/roles" :class="{ active: isActive('/dashboard/users/roles') }">
              نقش‌های کاربری
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/users/plans" :class="{ active: isActive('/dashboard/users/plans') }">
              پل‌های کاربری
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- محصولات (آکاردئونی) -->
      <li :class="{ open: isProductsOpen }">
        <a href="javascript:void(0)" @click="toggleProducts">
          <i class="fa fa-box"></i>
          <span class="link-text">محصولات</span>
          <!-- <i class="fa fa-chevron-down arrow"></i> -->
        </a>

        <ul class="submenu" v-show="isProductsOpen">
          <li>
            <NuxtLink to="/dashboard/products/add" :class="{ active: isActive('/dashboard/products/create') }">
              ایجاد محصول جدید
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/products" :class="{ active: isActive('/dashboard/products') }">
              لیست محصولات
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/products/categories"
              :class="{ active: isActive('/dashboard/products/categories') }">
              دسته‌بندی ها
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/products/features" :class="{ active: isActive('/dashboard/products/features') }">
              ویژگی‌ها
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/products/units" :class="{ active: isActive('/dashboard/products/units') }">
              واحد های اندازه گیری
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/products/brands" :class="{ active: isActive('/dashboard/products/brands') }">
              برندها
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- آمار -->
      <li>
        <NuxtLink to="/dashboard/stats" class="baseUrl" :class="{ active: isActive('/dashboard/stats') }">
          <i class="fa fa-chart-line"></i>
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

/* وضعیت باز بودن منوی محصولات */
const isProductsOpen = ref(false)

/* تشخیص active */
const isActive = (path) => {
  if (path === '/dashboard') return route.path === '/dashboard'
  return route.path.startsWith(path)
}

/* باز/بسته کردن دستی */
const toggleUsers = () => {
  isUsersOpen.value = !isUsersOpen.value
}

const toggleProducts = () => {
  isProductsOpen.value = !isProductsOpen.value
}

/* باز شدن خودکار بر اساس route */
watch(
  () => route.path,
  (path) => {
    isUsersOpen.value = path.startsWith('/dashboard/users')
    isProductsOpen.value = path.startsWith('/dashboard/products')
  },
  { immediate: true }
)
</script>
