<template>
  <div class="customers-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">لیست مشتریان</h4>
      <NuxtLink to="/accounting/customers/create" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>افزودن مشتری
      </NuxtLink>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <input
              v-model="searchQuery"
              type="text"
              class="form-control"
              placeholder="جستجو بر اساس نام یا موبایل..."
              @input="debouncedSearch"
            >
          </div>
          <div class="col-md-2">
            <button @click="clearFilters" class="btn btn-outline-secondary w-100">
              پاک کردن فیلترها
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
          </div>
        </div>

        <div v-else-if="customers.length === 0" class="text-center py-4">
          <i class="fa fa-users fa-3x text-muted mb-3"></i>
          <p class="text-muted">هیچ مشتری یافت نشد</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>نام مشتری</th>
                <th>موبایل</th>
                <th>مانده حساب</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="customer in customers" :key="customer.id">
                <td>{{ customer.name }}</td>
                <td>{{ customer.mobile }}</td>
                <td>
                  <span :class="customer.balance >= 0 ? 'text-success' : 'text-danger'">
                    {{ formatCurrency(customer.balance) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button @click="editCustomer(customer)" class="btn btn-outline-primary">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteCustomer(customer)" class="btn btn-outline-danger">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="d-flex justify-content-center mt-4">
          <nav>
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                <button @click="changePage(pagination.current_page - 1)" class="page-link">
                  قبلی
                </button>
              </li>
              <li
                v-for="page in visiblePages"
                :key="page"
                class="page-item"
                :class="{ active: page === pagination.current_page }"
              >
                <button @click="changePage(page)" class="page-link">{{ page }}</button>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                <button @click="changePage(pagination.current_page + 1)" class="page-link">
                  بعدی
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { navigateTo } from '#app'

definePageMeta({
  layout: 'accounting',
  title: 'لیست مشتریان'
})

const { $api } = useNuxtApp()

const customers = ref([])
const loading = ref(false)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
})
const searchQuery = ref('')

const visiblePages = computed(() => {
  const current = pagination.value.current_page
  const total = pagination.value.last_page
  const pages = []

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...', total)
    } else if (current >= total - 3) {
      pages.push(1, '...')
      for (let i = total - 4; i <= total; i++) {
        pages.push(i)
      }
    } else {
      pages.push(1, '...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...', total)
    }
  }

  return pages.filter(page => page !== '...' || typeof page === 'string')
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fa-IR', {
    style: 'currency',
    currency: 'IRR',
    minimumFractionDigits: 0
}).format(Math.abs(amount)).replace('IRR', amount >= 0 ? 'تومان' : 'تومان بدهکار')
}

let searchTimeout
const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadCustomers(1)
  }, 500)
}

const loadCustomers = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      search: searchQuery.value || undefined,
      per_page: 10
    }
    const response = await $api.get('/customers', { params })
    customers.value = response.data.data
    pagination.value = response.data.pagination
  } catch (error) {
    console.error('Error loading customers:', error)
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadCustomers(page)
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  loadCustomers(1)
}

const editCustomer = (customer) => {
  navigateTo(`/accounting/customers/${customer.id}/edit`)
}

const deleteCustomer = async (customer) => {
  if (confirm(`آیا از حذف مشتری "${customer.name}" اطمینان دارید؟`)) {
    try {
      await $api.delete(`/customers/${customer.id}`)
      loadCustomers(pagination.value.current_page)
    } catch (error) {
      console.error('Error deleting customer:', error)
      alert('خطا در حذف مشتری')
    }
  }
}

onMounted(() => {
  loadCustomers()
})
</script>

<style scoped>
.table th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #e9ecef;
  font-weight: 600;
}

.table td {
  vertical-align: middle;
}

.btn-group-sm .btn {
  padding: 0.25rem 0.5rem;
}

.page-link {
  color: #0d6efd;
}

.page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
}
</style>