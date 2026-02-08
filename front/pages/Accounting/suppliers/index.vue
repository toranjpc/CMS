<template>
  <div class="suppliers-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">لیست تأمین‌کنندگان</h4>
      <NuxtLink to="/accounting/suppliers/create" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>افزودن تأمین‌کننده
      </NuxtLink>
    </div>
    <div class="card">
      <div class="card-body text-center py-4 text-muted">
        لیست تأمین‌کنندگان در اینجا نمایش داده خواهد شد
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'accounting',
  title: 'لیست تأمین‌کنندگان'
})
</script>