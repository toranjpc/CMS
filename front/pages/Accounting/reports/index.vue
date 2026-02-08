<template>
  <div class="reports-page">
    <h4 class="mb-4">گزارشات</h4>
    <div class="row g-4">
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fa fa-chart-line fa-2x text-primary mb-3"></i>
            <h6>گزارش فروش</h6>
            <p class="text-muted small">گزارش فروش روزانه/ماهانه</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fa fa-dollar-sign fa-2x text-success mb-3"></i>
            <h6>گزارش سود</h6>
            <p class="text-muted small">گزارش سود تقریبی</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fa fa-users fa-2x text-warning mb-3"></i>
            <h6>بدهی مشتریان</h6>
            <p class="text-muted small">لیست بدهی مشتریان</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fa fa-truck fa-2x text-danger mb-3"></i>
            <h6>بدهی تأمین‌کنندگان</h6>
            <p class="text-muted small">لیست بدهی تأمین‌کنندگان</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fa fa-box fa-2x text-info mb-3"></i>
            <h6>موجودی کالا</h6>
            <p class="text-muted small">گزارش موجودی محصولات</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'accounting',
  title: 'گزارشات'
})
</script>