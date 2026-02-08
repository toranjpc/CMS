<template>
  <div>
    <h1>پنل مدیریت محتوا</h1>
    <p>خوش آمدید!</p>
    <NuxtLink class="btn " to="/dashboard">داشبورد</NuxtLink>
    <NuxtLink class="btn " to="/register">ثبت نام</NuxtLink>
  </div>
  <!-- </template> -->

  <!-- Features Section -->
  <div class="features-section py-5">
    <div class="container">
      <div class="row text-center mb-5">
        <div class="col-12">
          <h2 class="display-5 fw-bold">ویژگی‌های کلیدی</h2>
          <p class="lead text-muted">تمام آنچه برای مدیریت محتوای وبسایت نیاز دارید</p>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
              <div class="feature-icon mb-3">
                <i class="bi bi-speedometer2 text-primary fs-1"></i>
              </div>
              <h5 class="card-title fw-bold">کارایی بالا</h5>
              <p class="card-text text-muted">
                سیستم بهینه شده برای عملکرد سریع و پاسخگویی عالی
              </p>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
              <div class="feature-icon mb-3">
                <i class="bi bi-shield-check text-success fs-1"></i>
              </div>
              <h5 class="card-title fw-bold">امنیت کامل</h5>
              <p class="card-text text-muted">
                حفاظت پیشرفته از داده‌ها و اطلاعات کاربران
              </p>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
              <div class="feature-icon mb-3">
                <i class="bi bi-phone text-info fs-1"></i>
              </div>
              <h5 class="card-title fw-bold">طراحی ریسپانسیو</h5>
              <p class="card-text text-muted">
                سازگار با تمام دستگاه‌ها و اندازه‌های صفحه نمایش
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Section -->
  <div class="stats-section bg-light py-5">
    <div class="container">
      <div class="row text-center g-4">
        <div class="col-md-3">
          <div class="stat-item">
            <h3 class="display-4 fw-bold text-primary mb-2">1000+</h3>
            <p class="text-muted mb-0">کاربر فعال</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-item">
            <h3 class="display-4 fw-bold text-primary mb-2">5000+</h3>
            <p class="text-muted mb-0">صفحه محتوا</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-item">
            <h3 class="display-4 fw-bold text-primary mb-2">99.9%</h3>
            <p class="text-muted mb-0">در دسترس بودن</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-item">
            <h3 class="display-4 fw-bold text-primary mb-2">24/7</h3>
            <p class="text-muted mb-0">پشتیبانی</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Check if user is already authenticated
onMounted(() => {
  if (process.client) {
    const token = localStorage.getItem('token')
    const user = localStorage.getItem('user')

    if (token && user) {
      // User is authenticated, redirect to dashboard
      router.push('/dashboard')
    }
  }
})

definePageMeta({
  title: 'خانه - CMS Panel',
  layout: false
})
</script>

<style scoped>
.hero-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 60vh;
  display: flex;
  align-items: center;
}

.feature-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(0, 123, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

.stat-item h3 {
  font-size: 2.5rem;
}
</style>
