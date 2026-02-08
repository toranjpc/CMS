<template>
  <div class="dashboard-page">
    <div class="row mb-4">
      <div class="col-12">
        <h1 class="fw-bold">داشبورد حسابداری</h1>
        <p class="text-muted">خوش آمدید به سیستم حسابداری</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">فروش امروز</h5>
                <h2 class="mb-0">{{ formatCurrency(metrics.today_sales) }}</h2>
              </div>
              <i class="bi bi-graph-up-arrow fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">دریافت امروز</h5>
                <h2 class="mb-0">{{ formatCurrency(metrics.today_receipts) }}</h2>
              </div>
              <i class="bi bi-cash-coin fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">پرداخت امروز</h5>
                <h2 class="mb-0">{{ formatCurrency(metrics.today_payments) }}</h2>
              </div>
              <i class="bi bi-credit-card fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">موجودی صندوق</h5>
                <h2 class="mb-0">{{ formatCurrency(metrics.cash_balance) }}</h2>
              </div>
              <i class="bi bi-safe fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- More Stats -->
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <div class="card bg-secondary text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">موجودی بانک</h5>
                <h2 class="mb-0">{{ formatCurrency(metrics.bank_balance) }}</h2>
              </div>
              <i class="bi bi-bank fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-danger text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">بدهی مشتریان</h5>
                <h2 class="mb-0">{{ formatCurrency(metrics.customer_debt) }}</h2>
              </div>
              <i class="bi bi-person-dash fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-dark text-white h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-1">بدهی تأمین‌کنندگان</h5>
                <h2 class="mb-0">{{ formatCurrency(metrics.supplier_debt) }}</h2>
              </div>
              <i class="bi bi-truck fs-1 opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">دسترسی سریع</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-3">
                <NuxtLink to="/accounting/products/create" class="btn btn-success w-100">
                  <i class="fa fa-plus me-2"></i>محصول جدید
                </NuxtLink>
              </div>
              <div class="col-md-3">
                <NuxtLink to="/accounting/customers/create" class="btn btn-primary w-100">
                  <i class="fa fa-user-plus me-2"></i>مشتری جدید
                </NuxtLink>
              </div>
              <div class="col-md-3">
                <NuxtLink to="/accounting/sales/create" class="btn btn-info w-100">
                  <i class="fa fa-shopping-cart me-2"></i>فاکتور فروش
                </NuxtLink>
              </div>
              <div class="col-md-3">
                <NuxtLink to="/accounting/receipts/create" class="btn btn-warning w-100">
                  <i class="fa fa-money-bill-wave me-2"></i>دریافت وجه
                </NuxtLink>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: 'accounting',
  title: 'داشبورد حسابداری'
})

const { $api } = useNuxtApp()

const metrics = ref({
  today_sales: 0,
  today_receipts: 0,
  today_payments: 0,
  cash_balance: 0,
  bank_balance: 0,
  customer_debt: 0,
  supplier_debt: 0
})
const loading = ref(false)

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fa-IR', {
    style: 'currency',
    currency: 'IRR',
    minimumFractionDigits: 0
}).format(amount).replace('IRR', 'تومان')
}

const loadMetrics = async () => {
  loading.value = true
  try {
    const response = await $api.get('/dashboard/metrics')
    metrics.value = response.data
  } catch (error) {
    console.error('Error loading metrics:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadMetrics()
})
</script>

<style scoped>
.card {
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.card:hover {
  transform: translateY(-2px);
}

.card-body {
  padding: 1.5rem;
}

.btn {
  padding: 0.75rem 1rem;
  font-weight: 500;
}

.card-header {
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  font-weight: 600;
}
</style>