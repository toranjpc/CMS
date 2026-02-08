<template>
  <div class="settings-page">
    <h4 class="mb-4">تنظیمات</h4>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">اطلاعات فروشگاه</h6>
          </div>
          <div class="card-body text-center py-4 text-muted">
            فرم اطلاعات فروشگاه در اینجا قرار خواهد گرفت
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">موجودی‌های اولیه</h6>
          </div>
          <div class="card-body text-center py-4 text-muted">
            تنظیمات موجودی‌های اولیه در اینجا قرار خواهد گرفت
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'accounting',
  title: 'تنظیمات'
})
</script>