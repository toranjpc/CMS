<template>
  <nav class="navbar" :class="{ expanded: !collapsed }">
    <div class="d-flex align-items-center justify-content-between w-100">

      <!-- Title -->
      <div class="navbar-title">
        <h5 class="mb-0">{{ currentPageTitle }}</h5>
      </div>

      <!-- User Info & Logout -->
      <div class="user-menu dropdown">
        <button class="btn dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="user-avatar me-2">
            <i class="fa fa-user-circle fs-4 text-primary"></i>
          </div>
          <div class="user-info d-none d-md-block text-end">
            <div class="user-name fw-semibold">{{ user?.name || 'کاربر' }}</div>
            <small class="user-role">حسابدار</small>
          </div>
        </button>
        <ul class="dropdown-menu dropdown-menu-end text-end">
          <li>
            <a class="dropdown-item" href="#" @click.prevent="showProfile">
              <i class="fa fa-user me-2"></i>
              پروفایل
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#" @click.prevent="showSettings">
              <i class="fa fa-cog me-2"></i>
              تنظیمات
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item text-danger" href="#" @click.prevent="logout">
              <i class="fa fa-sign-out-alt me-2"></i>
              خروج
            </a>
          </li>
        </ul>
      </div>

    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '~/components/useAuth'

defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
})

const route = useRoute()
const router = useRouter()
const authStore = useAuth()
const user = computed(() => authStore.user.value)

const currentPageTitle = computed(() => {
  const titles = {
    '/accounting': 'داشبورد حسابداری',
    '/accounting/products': 'لیست محصولات',
    '/accounting/products/create': 'افزودن محصول',
    '/accounting/customers': 'لیست مشتریان',
    '/accounting/customers/create': 'افزودن مشتری',
    '/accounting/suppliers': 'لیست تأمین‌کنندگان',
    '/accounting/suppliers/create': 'افزودن تأمین‌کننده',
    '/accounting/sales': 'لیست فاکتورهای فروش',
    '/accounting/sales/create': 'ثبت فاکتور فروش',
    '/accounting/purchases': 'لیست خریدها',
    '/accounting/purchases/create': 'ثبت خرید',
    '/accounting/receipts': 'لیست دریافت‌ها',
    '/accounting/receipts/create': 'ثبت دریافت',
    '/accounting/payments': 'لیست پرداخت‌ها',
    '/accounting/payments/create': 'ثبت پرداخت',
    '/accounting/cashbox': 'مدیریت صندوق',
    '/accounting/banks': 'حساب‌های بانکی',
    '/accounting/expenses': 'لیست هزینه‌ها',
    '/accounting/expenses/create': 'ثبت هزینه',
    '/accounting/reports': 'گزارشات',
    '/accounting/settings': 'تنظیمات'
  }
  return titles[route.path] || 'سیستم حسابداری'
})

const showProfile = () => {
  alert('پروفایل - در آینده پیاده‌سازی خواهد شد')
}

const showSettings = () => {
  router.push('/accounting/settings')
}

const logout = async () => {
  if (confirm('آیا از خروج اطمینان دارید؟')) {
    try {
      await authStore.logout()
    } catch (error) {
      authStore.clearAuth()
      await navigateTo('/login')
    }
  }
}

</script>

<style scoped>
.navbar {
  background: #fff;
  border-bottom: 1px solid #e9ecef;
  padding: 0.75rem 1.5rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar.expanded {
  margin-left: 0;
}

.navbar-title {
  flex: 1;
}

.navbar-title h5 {
  color: #495057;
  font-weight: 600;
}

.user-menu .btn {
  border: none;
  background: transparent;
  color: #495057;
  font-size: 0.9rem;
}

.user-menu .btn:hover {
  background: rgba(0, 0, 0, 0.05);
}

.user-avatar {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(0, 123, 255, 0.1);
}

.user-name {
  font-size: 0.9rem;
  line-height: 1.2;
}

.user-role {
  font-size: 0.75rem;
  line-height: 1.1;
}

.dropdown-menu {
  border: 1px solid #e9ecef;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.dropdown-item:hover {
  background: rgba(0, 123, 255, 0.1);
}

.dropdown-item.text-danger:hover {
  background: rgba(220, 53, 69, 0.1);
}
</style>

<style scoped>
.navbar {
  background: #fff;
  border-bottom: 1px solid #e9ecef;
  padding: 0.75rem 1.5rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar.expanded {
  margin-left: 0;
}

.navbar-title {
  flex: 1;
}

.navbar-title h5 {
  color: #495057;
  font-weight: 600;
}

.user-menu .btn {
  border: none;
  background: transparent;
  color: #495057;
  font-size: 0.9rem;
}

.user-menu .btn:hover {
  background: rgba(0, 0, 0, 0.05);
}

.user-avatar {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(0, 123, 255, 0.1);
}

.user-name {
  font-size: 0.9rem;
  line-height: 1.2;
}

.user-role {
  font-size: 0.75rem;
  line-height: 1.1;
}

.dropdown-menu {
  border: 1px solid #e9ecef;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.dropdown-item:hover {
  background: rgba(0, 123, 255, 0.1);
}

.dropdown-item.text-danger:hover {
  background: rgba(220, 53, 69, 0.1);
}
</style>
