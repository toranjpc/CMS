<template>
  <div class="products-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">لیست محصولات</h4>
      <NuxtLink to="/accounting/products/create" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>افزودن محصول
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
              placeholder="جستجو بر اساس نام یا کد محصول..."
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

    <!-- Products Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
          </div>
        </div>

        <div v-else-if="products.length === 0" class="text-center py-4">
          <i class="fa fa-box fa-3x text-muted mb-3"></i>
          <p class="text-muted">هیچ محصولی یافت نشد</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>کد محصول</th>
                <th>نام محصول</th>
                <th>قیمت خرید</th>
                <th>قیمت فروش</th>
                <th>موجودی</th>
                <th>حداقل موجودی</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id">
                <td>{{ product.code }}</td>
                <td>{{ product.name }}</td>
                <td>{{ formatCurrency(product.buy_price) }}</td>
                <td>{{ formatCurrency(product.sell_price) }}</td>
                <td>
                  <span :class="getStockClass(product)">
                    {{ product.stock }}
                  </span>
                </td>
                <td>{{ product.min_stock }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button @click="editProduct(product)" class="btn btn-outline-primary">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteProduct(product)" class="btn btn-outline-danger">
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

definePageMeta({
  layout: 'accounting',
  title: 'لیست محصولات'
})

const { $api } = useNuxtApp()

const products = ref([])
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
}).format(amount).replace('IRR', 'تومان')
}

const getStockClass = (product) => {
  if (product.stock <= product.min_stock) return 'text-danger fw-bold'
  if (product.stock <= product.min_stock * 1.5) return 'text-warning'
  return 'text-success'
}

let searchTimeout
const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadProducts(1)
  }, 500)
}

const loadProducts = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      search: searchQuery.value || undefined,
      per_page: 10
    }
    const response = await $api.get('/products', { params })
    products.value = response.data.data
    pagination.value = response.data.pagination
  } catch (error) {
    console.error('Error loading products:', error)
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadProducts(page)
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  loadProducts(1)
}

const editProduct = (product) => {
  navigateTo(`/accounting/products/${product.id}/edit`)
}

const deleteProduct = async (product) => {
  if (confirm(`آیا از حذف محصول "${product.name}" اطمینان دارید؟`)) {
    try {
      await $api.delete(`/products/${product.id}`)
      loadProducts(pagination.value.current_page)
    } catch (error) {
      console.error('Error deleting product:', error)
      alert('خطا در حذف محصول')
    }
  }
}

onMounted(() => {
  loadProducts()
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