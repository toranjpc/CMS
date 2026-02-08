<template>
  <div class="expenses-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">لیست هزینه‌ها</h4>
      <NuxtLink to="/accounting/expenses/create" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>ثبت هزینه
      </NuxtLink>
    </div>
    <div class="card">
      <div class="card-body text-center py-4 text-muted">
        لیست هزینه‌ها در اینجا نمایش داده خواهد شد
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'accounting',
  title: 'لیست هزینه‌ها'
})
</script>