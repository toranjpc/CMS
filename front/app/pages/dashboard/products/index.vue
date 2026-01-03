<template>
  <div class="products-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">مدیریت محصولات</h1>
          <p class="text-muted mt-1">مدیریت و مشاهده لیست محصولات سیستم</p>
          <div v-if="selectedTr.length > 0" class="mt-2">
            <small class="text-primary">
              <i class="fa fa-check-circle me-1"></i>
              {{ selectedTr.length }} سطر انتخاب شده
            </small>
          </div>
        </div>
        <div class="d-flex gap-2">
          <button v-if="selectedTr.length > 0" class="btn btn-outline-danger"
            @click="bulkDeleteSelected" title="حذف گروهی">
            <i class="fa fa-trash me-1"></i>
            حذف انتخاب شده‌ها ({{ selectedTr.length }})
          </button>
          <button class="btn btn-primary" @click="goToAdd">
            <i class="fa fa-plus-circle me-1"></i>
            افزودن محصول
          </button>
        </div>
      </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
      <div class="card-body">
        <form @submit.prevent="searchFilters">
          <div class="row g-3">
            <!-- Row 1 -->
            <div class="col-md-3">
              <input type="text" class="form-control" placeholder="عنوان محصول..." v-model="searchQuery.title" />
            </div>
            <div class="col-md-2">
              <input type="number" class="form-control" placeholder="قیمت از..." v-model.number="searchQuery.price_from" />
            </div>
            <div class="col-md-2">
              <input type="number" class="form-control" placeholder="قیمت تا..." v-model.number="searchQuery.price_to" />
            </div>
            <div class="col-md-2">
              <select class="form-select" v-model="searchQuery.status">
                <option value="">همه وضعیت‌ها</option>
                <option value="1">انتشار</option>
                <option value="2">منتشر نشده</option>
              </select>
            </div>
            <div class="col-md-3">
              <input type="text" class="form-control" placeholder="کلمات کلیدی..." v-model="searchQuery.tags" />
            </div>

            <!-- Row 2 -->
            <div class="col-md-2">
              <select class="form-select" v-model="searchQuery.brand_id">
                <option value="">همه برندها</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                  {{ brand.title }}
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <select class="form-select" v-model="searchQuery.category_id">
                <option value="">همه دسته‌بندی‌ها</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.title }}
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <select class="form-select" v-model="searchQuery.feature_id">
                <option value="">همه ویژگی‌ها</option>
                <option v-for="feature in features" :key="feature.id" :value="feature.id">
                  {{ feature.title }}
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <select class="form-select" v-model="searchQuery.unit_id">
                <option value="">همه واحدها</option>
                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                  {{ unit.title }}
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="trashed" v-model="searchQuery.trashed">
                <label class="form-check-label" for="trashed">
                  نمایش حذف شده‌ها
                </label>
              </div>
            </div>

            <!-- Actions -->
            <div class="col-12" v-if="hasActiveFilters">
              <button type="submit" class="btn btn-success btn-sm me-2">
                <i class="fa fa-search me-1"></i>
                جستجو
              </button>
              <button type="button" class="btn btn-warning btn-sm" @click="resetFilters">
                <i class="fa fa-times me-1"></i>
                پاک کردن فیلترها
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- products Table -->
    <div class="card">
      <div class="card-body">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
          </div>
          <div class="mt-2 text-muted">در حال بارگذاری لیست محصولات...</div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <i class="fa fa-exclamation-triangle me-2"></i>
          {{ error }}
          <button class="btn btn-sm btn-outline-danger ms-2" @click="getData(itemsPerPage, currentPage, 0)">
            <i class="fa fa-arrow-clockwise"></i>
            تلاش مجدد
          </button>
        </div>

        <!-- Products Table -->
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th width="6%">شناسه <small class="text-muted">(کلیک برای انتخاب)</small></th>
                <th width="">عنوان محصول</th>
                <th width="12%">قیمت</th>
                <th width="8%">موجودی</th>
                <th width="8%">وضعیت</th>
                <th width="12%">تاریخ ایجاد</th>
                <th width="15%">عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(product, index) in products" :key="product.id" :class="getRowClass(product.id, index)"
                @click="setCurrentRow(index)">
                <td @click="toggleRowSelection(product.id)">
                  <input type="checkbox" v-if="selectedTr.includes(product.id)" checked class="form-check-input me-2">
                  <a href="javascript:;" v-else class="text-decoration-none">{{ product.id }}</a>
                </td>
                <td>
                  <div class="fw-semibold">{{ product.title }}</div>
                  <small class="text-muted" v-if="product.tags">{{ product.tags }}</small>
                </td>
                <td>
                  <div class="text-success fw-semibold">{{ formatPrice(product.firstPrice) }}</div>
                  <small class="text-muted" v-if="product.sell_price">{{ formatPrice(product.sell_price) }} (بدون تخفیف)</small>
                </td>
                <td>
                  <span class="badge" :class="getStockBadgeClass(product.firstWarehouse)">
                    {{ product.firstWarehouse }}
                  </span>
                </td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(product.status)">
                    {{ getStatusLabel(product.status) }}
                  </span>
                </td>
                <td>
                  <small class="" dir="ltr" v-html="formatDate(product.created_at)"></small>
                </td>
                <td>
                  <div class="actionBTN btn-group btn-group-sm float-end">
                    <button class="btn text-primary btn-sm" @click="viewProduct(product)" title="مشاهده">
                      <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn text-warning btn-sm" @click="editProduct(product)" title="ویرایش">
                      <i class="fa fa-pencil"></i>
                    </button>
                    <button v-if="!product.deleted_at" class="btn text-danger btn-sm" @click="deleteProduct(product)" title="حذف">
                      <i class="fa fa-times"></i>
                    </button>
                    <button v-else class="btn text-success btn-sm" @click="restoreProduct(product)" title="بازیابی">
                      <i class="fa fa-refresh"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <!-- Empty State -->
              <tr v-if="totalitems === 0">
                <td colspan="7" class="text-center py-5">
                  <div class="text-muted">
                    <i class="fa fa-box display-4 mb-3"></i>
                    <div>هیچ محصولی یافت نشد</div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted">
            نمایش {{ (currentPage - 1) * itemsPerPage + 1 }} تا {{ Math.min(currentPage * itemsPerPage,
              totalitems) }} از {{ totalitems }} محصول

          </div>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" href="javascript:;"
                  @click="getData(itemsPerPage, currentPage - 1, 0, getSearchParams())" v-if="currentPage > 1">قبلی</a>
              </li>

              <li class="page-item" :class="{ active: page === currentPage }" v-for="page in totalPages" :key="page">
                <a class="page-link" href="javascript:;" @click="getData(itemsPerPage, page, 0, getSearchParams())">{{
                  page }}</a>
              </li>

              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link" href="javascript:;"
                  @click="getData(itemsPerPage, parseInt(currentPage) + 1, 0, getSearchParams())"
                  v-if="currentPage < totalPages">بعدی</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import Swal from 'sweetalert2'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
  title: 'مدیریت محصولات'
})

const { $api } = useNuxtApp()

const products = ref([])
const loading = ref(false)
const error = ref(null)
const totalitems = ref(0)
const totalPages = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(10)
const searchQuery = ref({
  title: '',
  price_from: null,
  price_to: null,
  status: '',
  tags: '',
  brand_id: '',
  category_id: '',
  feature_id: '',
  unit_id: '',
  trashed: false
})

const brands = ref([])
const categories = ref([])
const features = ref([])
const units = ref([])
const selectedTr = ref([])
const currentRowIndex = ref(-1)

const getData = async (iPP, cP, searchParams = {}) => {
  loading.value = true
  error.value = null

  try {
    let url = `/products?limit=${iPP}&page=${cP}`

    // استفاده از پارامترهای جستجو ارسالی یا پارامترهای فعلی
    const paramsToUse = Object.keys(searchParams).length ? searchParams : searchQuery.value
    const keys = Object.keys(paramsToUse)
    if (keys.length) {
      const queryString = keys
        .filter(key => paramsToUse[key] !== '' && paramsToUse[key] !== null && paramsToUse[key] !== undefined)
        .map(key => `${key}=${encodeURIComponent(paramsToUse[key])}`)
        .join('&')
      url += "&" + queryString
    }

    const response = await $api(url)
    products.value = response.data?.items || []
    totalitems.value = response.data?.total || 0
    totalPages.value = response.data?.last_page || 0
    currentPage.value = response.data?.current_page || 1

    // ریست کردن انتخاب‌ها وقتی داده‌ها تغییر می‌کنند
    selectedTr.value = []
    currentRowIndex.value = -1 // هیچ سطری انتخاب نشده

  } catch (err) {
    error.value = 'خطا در بارگذاری لیست محصولات'
  } finally {
    loading.value = false
  }
}

// بارگذاری داده‌های اولیه
onMounted(async () => {
  await loadFilterData()
  getData(itemsPerPage.value, currentPage.value)
  setupKeyboardNavigation()
})

// بارگذاری داده‌های فیلتر
const loadFilterData = async () => {
  try {
    const [brandsRes, categoriesRes, featuresRes, unitsRes] = await Promise.all([
      $api('/products/brands?all=1'),
      $api('/products/categories?all=1'),
      $api('/products/features?all=1'),
      $api('/products/units?all=1')
    ])

    brands.value = brandsRes.data?.items || []
    categories.value = categoriesRes.data?.items || []
    features.value = featuresRes.data?.items || []
    units.value = unitsRes.data?.items || []
  } catch (err) {
    console.error('Error loading filter data:', err)
  }
}

// Keyboard navigation setup
const setupKeyboardNavigation = () => {
  const handleKeyDown = (event) => {
    // اگر فوکوس روی input یا textarea باشد، کلیدها را پردازش نکن
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.tagName === 'SELECT') {
      return
    }

    switch (event.key) {
      case 'ArrowUp':
        event.preventDefault()
        navigateRows(-1)
        break
      case 'ArrowDown':
        event.preventDefault()
        navigateRows(1)
        break
      case 'ArrowLeft':
      case 'ArrowRight':
        // برای products.vue، کلیدهای چپ و راست کاری انجام نمی‌دهند
        break
      case ' ':
        event.preventDefault()
        toggleCurrentRowSelection()
        break
      case 'Enter':
        event.preventDefault()
        performCurrentRowAction()
        break
    }
  }

  // اضافه کردن event listener
  document.addEventListener('keydown', handleKeyDown)

  // پاک کردن event listener هنگام unmount
  onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown)
  })
}

const navigateRows = (direction) => {
  const totalRows = products.value.length
  if (totalRows === 0) return

  // اگر هیچ سطری انتخاب نشده، اولین حرکت کاربر را به اولین سطر ببر
  if (currentRowIndex.value === -1) {
    currentRowIndex.value = direction > 0 ? 0 : totalRows - 1
    return
  }

  // حرکت به سطر بعدی/قبلی
  currentRowIndex.value += direction

  // محدود کردن به محدوده مجاز
  if (currentRowIndex.value < 0) {
    currentRowIndex.value = 0
  } else if (currentRowIndex.value >= totalRows) {
    currentRowIndex.value = totalRows - 1
  }
}

const setCurrentRow = (index) => {
  currentRowIndex.value = index
}

const toggleCurrentRowSelection = () => {
  if (currentRowIndex.value === -1 || !products.value[currentRowIndex.value]) return

  const userId = products.value[currentRowIndex.value].id
  toggleRowSelection(userId)
}

const toggleRowSelection = (userId) => {
  const index = selectedTr.value.indexOf(userId)
  if (index > -1) {
    // حذف از لیست انتخاب شده
    selectedTr.value.splice(index, 1)
  } else {
    // اضافه کردن به لیست انتخاب شده
    selectedTr.value.push(userId)
  }
}

const getRowClass = (userId, index) => {
  const classes = []

  if (selectedTr.value.includes(userId)) {
    classes.push('selected')
  }

  if (index === currentRowIndex.value) {
    classes.push('current-row')
  }

  return classes.join(' ')
}

const bulkDeleteSelected = async () => {
  const result = await Swal.fire({
    title: 'تأیید حذف گروهی',
    text: `آیا مطمئن هستید که می‌خواهید ${selectedTr.value.length} محصول انتخاب شده را حذف کنید؟`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'بله، حذف کن',
    cancelButtonText: 'لغو',
    reverseButtons: true,
    customClass: {
      popup: 'swal-rtl'
    }
  })

  if (!result.isConfirmed) return

  let successCount = 0
  let errorCount = 0

  for (const productId of selectedTr.value) {
    try {
      await $api(`/products/${productId}`, {
        method: 'DELETE'
      })
      successCount++
    } catch (err) {
      errorCount++
    }
  }

  selectedTr.value = []
  currentRowIndex.value = -1

  await getData(itemsPerPage.value, currentPage.value)

  if (errorCount === 0) {
    await Swal.fire({
      title: 'انجام شد!',
      text: `${successCount} محصول با موفقیت حذف شد.`,
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
      customClass: {
        popup: 'swal-rtl'
      }
    })
  } else {
    await Swal.fire({
      title: 'عملیات نیمه کامل',
      text: `${successCount} محصول حذف شد. ${errorCount} محصول با خطا مواجه شد.`,
      icon: 'warning',
      customClass: {
        popup: 'swal-rtl'
      }
    })
  }
}

const performCurrentRowAction = () => {
  if (currentRowIndex.value === -1 || !products.value[currentRowIndex.value]) return

  const selectedProduct = products.value[currentRowIndex.value]
  editProduct(selectedProduct)
}

// Navigation functions
const goToAdd = () => {
  navigateTo('/dashboard/products/add')
}

const viewProduct = (product) => {
  navigateTo(`/dashboard/products/${product.id}`)
}

const editProduct = (product) => {
  navigateTo(`/dashboard/products/edit_${product.id}`)
}

const deleteProduct = async (product) => {
  const result = await Swal.fire({
    title: 'تأیید حذف',
    text: `آیا مطمئن هستید که می‌خواهید محصول "${product.title}" را حذف کنید؟`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'بله، حذف کن',
    cancelButtonText: 'لغو',
    reverseButtons: true,
    customClass: {
      popup: 'swal-rtl'
    }
  })

  if (result.isConfirmed) {
    try {
      await $api(`/products/${product.id}`, {
        method: 'DELETE'
      })

      await Swal.fire({
        title: 'انجام شد!',
        text: 'محصول با موفقیت حذف شد.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        customClass: {
          popup: 'swal-rtl'
        }
      })

      await getData(itemsPerPage.value, currentPage.value)
    } catch (err) {
      Swal.fire({
        title: 'خطا!',
        text: 'خطا در حذف محصول',
        icon: 'error',
        customClass: {
          popup: 'swal-rtl'
        }
      })
    }
  }
}

const restoreProduct = async (product) => {
  const result = await Swal.fire({
    title: 'تأیید بازیابی',
    text: `آیا مطمئن هستید که می‌خواهید محصول "${product.title}" را بازیابی کنید؟`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'بله، بازیابی کن',
    cancelButtonText: 'لغو',
    reverseButtons: true,
    customClass: {
      popup: 'swal-rtl'
    }
  })

  if (result.isConfirmed) {
    try {
      await $api(`/products/${product.id}/restore`, {
        method: 'patch',
        body: { status: 1 }
      })

      await Swal.fire({
        title: 'انجام شد!',
        text: 'محصول با موفقیت بازیابی شد.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        customClass: {
          popup: 'swal-rtl'
        }
      })

      await getData(itemsPerPage.value, currentPage.value)
    } catch (err) {
      Swal.fire({
        title: 'خطا!',
        text: 'خطا در بازیابی محصول',
        icon: 'error',
        customClass: {
          popup: 'swal-rtl'
        }
      })
    }
  }
}

// Helper functions for display
const getStatusLabel = (status) => {
  return status == 1 ? 'انتشار' : 'منتشر نشده'
}

const getStatusBadgeClass = (status) => {
  return status == 1 ? 'bg-success' : 'bg-warning'
}

const getStockBadgeClass = (stock) => {
  if (stock <= 0) return 'bg-danger'
  if (stock < 10) return 'bg-warning'
  return 'bg-success'
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fa-IR').format(price) + ' تومان'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  let dateinit = new Date(dateString)
  let date = dateinit.toLocaleDateString('fa-IR')
  let time = dateinit.toLocaleTimeString('fa-IR')
  return `${date} <span class="text-muted">${time}</span>`
}

// تبدیل تاریخ میلادی به فارسی برای نمایش
const formatPersianDate = (dateString = null) => {
  if (!dateString) {
    // اگر تاریخ خالی بود، تاریخ روز را برگردان
    const today = new Date()
    const persianDate = new Intl.DateTimeFormat('fa-IR', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    }).format(today)
    return persianDate
  }

  try {
    // اگر تاریخ قبلاً فارسی است، آن را برگردان
    if (dateString.match(/^\d{4}\/\d{2}\/\d{2}$/)) {
      return dateString
    }

    const date = new Date(dateString)

    // تبدیل به تاریخ فارسی
    const persianDate = new Intl.DateTimeFormat('fa-IR', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    }).format(date)

    return persianDate
  } catch (e) {
    return dateString || ''
  }
}

// تبدیل تاریخ فارسی به میلادی برای ارسال به سرور
const convertPersianToGregorian = (persianDate) => {
  if (!persianDate) return null

  try {
    // اگر تاریخ قبلاً میلادی است، آن را برگردان
    if (persianDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
      return persianDate
    }

    // پاک کردن کاراکترهای غیر عددی و /
    const cleaned = persianDate.replace(/[^\d/]/g, '')

    // تبدیل تاریخ فارسی به میلادی (تبدیل ساده)
    const persianDateParts = cleaned.split('/')
    if (persianDateParts.length === 3) {
      const [year, month, day] = persianDateParts.map(Number)

      // تبدیل سال شمسی به میلادی (تقریبی)
      const gregorianYear = year + 621
      const gregorianMonth = month
      const gregorianDay = day

      // ایجاد تاریخ میلادی
      const gregorianDate = new Date(gregorianYear, gregorianMonth - 1, gregorianDay)

      // اگر تاریخ نامعتبر است، null برگردان
      if (isNaN(gregorianDate.getTime())) {
        return null
      }

      return gregorianDate.toISOString().split('T')[0]
    }

    return null
  } catch (e) {
    return null
  }
}

const searchFilters = () => {
  getData(itemsPerPage.value, currentPage.value)
}

const resetFilters = () => {
  searchQuery.value = {
    title: '',
    price_from: null,
    price_to: null,
    status: '',
    tags: '',
    brand_id: '',
    category_id: '',
    feature_id: '',
    unit_id: '',
    trashed: false
  }
  selectedTr.value = []
  currentRowIndex.value = -1
  getData(itemsPerPage.value, currentPage.value)
}

// تابع برای گرفتن پارامترهای جستجوی فعلی
const getSearchParams = () => {
  const params = {}

  if (searchQuery.value.title) params.title = searchQuery.value.title
  if (searchQuery.value.price_from !== null) params.price_from = searchQuery.value.price_from
  if (searchQuery.value.price_to !== null) params.price_to = searchQuery.value.price_to
  if (searchQuery.value.status !== '') params.status = searchQuery.value.status
  if (searchQuery.value.tags) params.tags = searchQuery.value.tags
  if (searchQuery.value.brand_id) params.brand_id = searchQuery.value.brand_id
  if (searchQuery.value.category_id) params.category_id = searchQuery.value.category_id
  if (searchQuery.value.feature_id) params.feature_id = searchQuery.value.feature_id
  if (searchQuery.value.unit_id) params.unit_id = searchQuery.value.unit_id
  if (searchQuery.value.trashed) params.trashed = 'true'

  return params
}






const hasActiveFilters = computed(() => {
  return searchQuery.value.title ||
    searchQuery.value.price_from !== null ||
    searchQuery.value.price_to !== null ||
    searchQuery.value.status !== '' ||
    searchQuery.value.tags ||
    searchQuery.value.brand_id ||
    searchQuery.value.category_id ||
    searchQuery.value.feature_id ||
    searchQuery.value.unit_id ||
    searchQuery.value.trashed
})
</script>

<style scoped>
.products-page {
  padding: 20px 0;
}

.table th {
  font-weight: 600;
  border-bottom: 2px solid #dee2e6;
}

.page-link {
  color: #0d6efd;
}

.page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.selected {
  background-color: #e3f2fd !important;
}

.current-row {
  background-color: #f8f9fa !important;
  border-left: 3px solid #007bff;
}

/* Action buttons styling */
.actionBTN {
  white-space: nowrap;
}

.actionBTN .btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  line-height: 1;
  border: none;
  background: transparent;
}

.actionBTN .btn:hover {
  background: rgba(0, 0, 0, 0.1);
}

/* SweetAlert2 RTL Support */
.swal-rtl {
  direction: rtl;
}

.swal-rtl .swal2-popup .swal2-actions {
  flex-direction: row-reverse;
}
</style>
