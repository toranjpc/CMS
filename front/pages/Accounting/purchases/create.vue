<template>
  <div class="purchases-form-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">ثبت خرید</h4>
      <NuxtLink to="/accounting/purchases" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-right me-1"></i>بازگشت به لیست
      </NuxtLink>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="alert alert-info">
          <i class="fa fa-info-circle me-2"></i>
          فرم ثبت خرید در اینجا قرار خواهد گرفت
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
  title: 'ثبت خرید'
})
</script>