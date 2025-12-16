<template>
  <div class="dashboard-page">
    <div class="row mb-4">
      <div class="col-12">
        <h1 class="fw-bold">داشبورد</h1>
        <p class="text-muted">خوش آمدید به پنل مدیریت</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">کاربران کل</h5>
                <h2 class="mb-0">{{ stats.totalUsers }}</h2>
              </div>
              <i class="bi bi-people-fill fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">صفحات</h5>
                <h2 class="mb-0">{{ stats.totalPages }}</h2>
              </div>
              <i class="bi bi-file-earmark-text-fill fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">بازدیدها</h5>
                <h2 class="mb-0">{{ stats.totalVisits }}</h2>
              </div>
              <i class="bi bi-eye-fill fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">کامنت‌ها</h5>
                <h2 class="mb-0">{{ stats.totalComments }}</h2>
              </div>
              <i class="bi bi-chat-dots-fill fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
      <div class="col-md-8 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">فعالیت‌های اخیر</h5>
          </div>
          <div class="card-body">
            <div class="activity-item d-flex align-items-center mb-3 pb-3 border-bottom" v-for="activity in recentActivities" :key="activity.id">
              <div class="activity-icon me-3">
                <i :class="activity.icon" class="text-primary"></i>
              </div>
              <div class="flex-grow-1">
                <p class="mb-1 fw-semibold">{{ activity.title }}</p>
                <small class="text-muted">{{ activity.time }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">اطلاعات سیستم</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <small class="text-muted d-block">نسخه سیستم</small>
              <span class="fw-semibold">v1.0.0</span>
            </div>
            <div class="mb-3">
              <small class="text-muted d-block">آخرین بروزرسانی</small>
              <span class="fw-semibold">{{ new Date().toLocaleDateString('fa-IR') }}</span>
            </div>
            <div class="mb-3">
              <small class="text-muted d-block">وضعیت سرور</small>
              <span class="badge bg-success">آنلاین</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>

// Mock data - in real app this would come from API
const stats = reactive({
  totalUsers: 1250,
  totalPages: 345,
  totalVisits: 15420,
  totalComments: 892
})

const recentActivities = [
  {
    id: 1,
    title: 'کاربر جدید ثبت نام کرد',
    time: '۲ دقیقه پیش',
    icon: 'bi bi-person-plus'
  },
  {
    id: 2,
    title: 'صفحه جدید ایجاد شد',
    time: '۱۰ دقیقه پیش',
    icon: 'bi bi-file-earmark-plus'
  },
  {
    id: 3,
    title: 'نظر جدید تایید شد',
    time: '۱ ساعت پیش',
    icon: 'bi bi-check-circle'
  },
  {
    id: 4,
    title: 'تنظیمات به‌روزرسانی شد',
    time: '۲ ساعت پیش',
    icon: 'bi bi-gear'
  },
  {
    id: 5,
    title: 'فایل پشتیبان ایجاد شد',
    time: '۳ ساعت پیش',
    icon: 'bi bi-archive'
  }
]

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
  title: 'داشبورد - CMS Panel'
})
</script>

<style scoped>
.dashboard-page {
  padding: 20px 0;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(0, 123, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
