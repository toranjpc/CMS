<template>
  <div class="receipts-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">لیست دریافت‌ها</h4>
      <NuxtLink to="/accounting/receipts/create" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>ثبت دریافت
      </NuxtLink>
    </div>
    <div class="card">
      <div class="card-body text-center py-4 text-muted">
        لیست دریافت‌ها در اینجا نمایش داده خواهد شد
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'accounting',
  title: 'لیست دریافت‌ها'
})
</script>