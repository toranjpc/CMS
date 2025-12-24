<template>
  <nav class="navbar" :class="{ expanded: !collapsed }">
    <div class="d-flex align-items-center justify-content-between w-100">

      <!-- Notifications -->
      <div class="notifications dropdown mx-3">
        <button class="btn position-relative" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa fa-bell fs-5"></i>
          <span class="badge bg-danger notification-badge">3</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end text-end notification-menu">
          <li class="dropdown-header">اعلان‌ها</li>
          <li>
            <a class="dropdown-item" href="#">
              <i class="fa fa-user-plus text-success me-2"></i>
              کاربر جدید ثبت نام کرد
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#">
              <i class="fa fa-file-alt text-info me-2"></i>
              صفحه جدید ایجاد شد
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="#">
              <i class="fa fa-comment text-warning me-2"></i>
              نظر جدید دریافت شد
            </a>
          </li>
        </ul>
      </div>

      <!-- User Info & Logout -->
      <div class="user-menu dropdown">
        <button class="btn dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="user-avatar me-2">
            <i class="fa fa-user-circle fs-4 text-primary"></i>
          </div>
          <div class="user-info d-none d-md-block text-end">
            <div class="user-name fw-semibold">{{ user?.name || 'کاربر' }}</div>
            <small class="user-role">{{ user?.role || 'مدیر' }}</small>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
})

const router = useRouter()
const authStore = useAuth()
const user = computed(() => authStore.user.value)

const showProfile = () => {
  // TODO: Implement profile modal/page
  alert('پروفایل - در آینده پیاده‌سازی خواهد شد')
}

const showSettings = () => {
  // TODO: Implement settings modal/page
  alert('تنظیمات - در آینده پیاده‌سازی خواهد شد')
}

const logout = async () => {
  if (confirm('آیا از خروج اطمینان دارید؟')) {
    try {
      await authStore.logout()
    } catch (error) {
      //console.error('Logout error:', error)
      // Force logout even if API call fails
      authStore.clearAuth()
      await navigateTo('/login')
    }
  }
}
</script>

<style scoped>
.navbar {
  background: var(--bs-blue);
  border-bottom: 1px solid #e9ecef;
  padding: 0.75rem 1.5rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  color: #fff;
}

.navbar.expanded {
  margin-left: 0;
}

.user-menu .btn {
  border: none;
  background: transparent;
  color: #fff;
  font-size: 0.9rem;
}

.user-menu .btn:hover {
  background: rgba(0, 123, 255, 0.1);
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

.notifications .btn {
  border: none;
  background: transparent;
  color: #fff;
  position: relative;
}

.notifications .btn:hover {
  background: rgba(108, 117, 125, 0.1);
  /* color: #495057; */
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  font-size: 0.6rem;
  padding: 0.2rem 0.4rem;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-menu {
    width: max-content;
}

.notification-menu .dropdown-header {
  font-weight: 600;
  /* color: #495057; */
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.notification-menu .dropdown-item {
  padding: 0.75rem 1rem;
  font-size: 0.9rem;
}

.notification-menu .dropdown-item i {
  font-size: 0.8rem;
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
