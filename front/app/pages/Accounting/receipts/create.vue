<template>
  <div class="receipts-form-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">ثبت دریافت</h4>
      <NuxtLink to="/accounting/receipts" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-right me-1"></i>بازگشت به لیست
      </NuxtLink>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="alert alert-info">
          <i class="fa fa-info-circle me-2"></i>
          فرم ثبت دریافت در اینجا قرار خواهد گرفت
        </div>
        <div class="text-center">
          <NuxtLink to="/accounting" class="btn btn-primary">
            بازگشت به داشبورد
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'accounting',
  title: 'ثبت دریافت'
})
</script>