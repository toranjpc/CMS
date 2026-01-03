<template>
  <div class="sales-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">لیست فاکتورهای فروش</h4>
      <NuxtLink to="/accounting/sales/create" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>ثبت فاکتور فروش
      </NuxtLink>
    </div>
    <div class="card">
      <div class="card-body text-center py-4 text-muted">
        لیست فاکتورهای فروش در اینجا نمایش داده خواهد شد
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'accounting',
  title: 'لیست فاکتورهای فروش'
})
</script>