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
          <i class="fa fa-tags"></i>
          <span class="link-text">محصولات</span>
          <!-- <i class="fa fa-chevron-down arrow"></i> -->
        </a>

        <ul class="submenu" v-show="isProductsOpen">
          <li>
            <NuxtLink to="/dashboard/products/add" :class="{ active: isActive('/dashboard/products/add') }">
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
          <li>
            <NuxtLink to="/dashboard/products/warehouses"
              :class="{ active: isActive('/dashboard/products/warehouses') }">
              انبار ها
            </NuxtLink>
          </li>
        </ul>
      </li>

      <!-- عملیات حسابداری (آکاردئونی) -->
      <li :class="{ open: isAccountingOpen }">
        <a href="javascript:void(0)" @click="toggleAccounting">
          <i class="fa fa-file"></i>
          <span class="link-text">عملیات حسابداری</span>
          <!-- <i class="fa fa-chevron-down arrow"></i> -->
        </a>

        <ul class="submenu" v-show="isAccountingOpen">
          <li>
            <NuxtLink to="/dashboard/Accounting/buy_factor" :class="{ active: isActive('/dashboard/Accounting/buy_factor') }">
              فاکتور خرید
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/Accounting/buy_list" :class="{ active: isActive('/dashboard/Accounting/buy_list') }">
              لیست فاکتورهای خرید
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/Accounting/sell_factor" :class="{ active: isActive('/dashboard/Accounting/sell_factor') }">
             فاکتور فروش
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/Accounting/sell_list" :class="{ active: isActive('/dashboard/Accounting/sell_list') }">
              لیست فاکتورهای فروش
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/Accounting/pay_receipt"
              :class="{ active: isActive('/dashboard/Accounting/pay_receipt') }">
              ثبت سند دریافت/پرداخت
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/dashboard/Accounting/pay_receipt_list"
              :class="{ active: isActive('/dashboard/Accounting/pay_receipt_list') }">
              لیست اسناد دریافت/پرداخت
            </NuxtLink>
          </li>


        </ul>
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
    default: true
  }
})

const route = useRoute()

/* وضعیت باز بودن منوی کاربران */
const isUsersOpen = ref(false)

/* وضعیت باز بودن منوی محصولات */
const isProductsOpen = ref(false)
const isAccountingOpen = ref(false)

/* تشخیص active */
const isActive = (path) => {
  if (path === '/dashboard') return route.path === '/dashboard'
  return route.path == path// || route.path.startsWith(path)
}

/* باز/بسته کردن دستی */
const toggleUsers = () => {
  isUsersOpen.value = !isUsersOpen.value
}

const toggleProducts = () => {
  isProductsOpen.value = !isProductsOpen.value
}

const toggleAccounting = () => {
  isAccountingOpen.value = !isAccountingOpen.value
}

/* باز شدن خودکار بر اساس route */
watch(
  () => route.path,
  (path) => {
    isUsersOpen.value = path.startsWith('/dashboard/users')
    isProductsOpen.value = path.startsWith('/dashboard/products')
    isAccountingOpen.value = path.startsWith('/dashboard/Accounting')
  },
  { immediate: true }
)
</script>
