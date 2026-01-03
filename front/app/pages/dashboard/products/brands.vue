<template>
  <div class="product-brands-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">برندها</h1>
          <p class="text-muted mt-1">مدیریت برندها</p>
          <div v-if="selectedTr.length > 0" class="mt-2">
            <small class="text-primary">
              <i class="fa fa-check-circle me-1"></i>
              مجموع {{ selectedTr.length }} سطر انتخاب شده
            </small>
          </div>
        </div>
        <div class="d-flex gap-2">
          <button v-if="selectedTr.length > 0" class="btn btn-outline-danger btn-sm" @click="bulkDeleteSelected"
            title="حذف گروهی">
            <i class="fa fa-trash me-1"></i>
            حذف گروهی ({{ selectedTr.length }})
          </button>
          <button class="btn btn-primary" @click="openbrandModal('create')">
            <i class="fa fa-plus-circle me-1"></i>
            افزودن برند
          </button>
        </div>
      </div>
    </div>

    <!-- جستجو -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="searchBrands" class="mb-0" enctype='multipart/form-data'>
              <div class="row g-3">

                <div class="col-md-4">
                  <input type="text" class="form-control" placeholder="جستجو بر اساس عنوان برند..."
                    v-model="searchQuery.title" />
                </div>
                <div class="col-md-3">
                  <select class="form-select" v-model="searchQuery.status">
                    <option value="1">لیست فعال</option>
                    <option value="deleted">سطل زباله</option>
                  </select>
                </div>
                <div class="col-md-2" v-if="searchQuery.title || searchQuery.status !== '1'">
                  <button type="submit" class="btn border-success text-success mx-1">
                    <i class="fa fa-search"></i>
                  </button>
                  <button type="button" class="btn border-warning text-warning mx-1" @click="resetFilters">
                    <i class="fa fa-times"></i>
                  </button>
                </div>

              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- جدول برندها -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body p-0">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary spinner-border-sm" role="status">
                <span class="visually-hidden">در حال بارگذاری...</span>
              </div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="alert alert-danger m-3" role="alert">
              <i class="fa fa-exclamation-triangle me-2"></i>
              {{ error }}
              <button class="btn btn-sm btn-outline-danger ms-2" @click="getData()">
                <i class="fa fa-arrow-clockwise"></i>
                تلاش مجدد
              </button>
            </div>

            <!-- Table -->
            <div v-else class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th width="10%">شناسه</th>
                    <th width="15%">آیکون</th>
                    <th width="">عنوان برند</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(brand, index) in brands" :key="brand.id"
                    :class="getRowClass(brand.id, index)" @click="setCurrentRow(index)">
                    <td @click="toggleRowSelectionById(brand.id)">
                      <input type="checkbox" v-if="selectedTr.includes(brand.id)" checked
                        class="form-check-input me-2">
                      <a href="javascript:;" v-else class="text-decoration-none">{{ brand.id }}</a>
                    </td>
                    <td class="text-center">
                      <div style="width: 40px; height: 40px; margin: 0 auto;">
                        <img :src="getBrandIconUrl(brand.id, brand.updated_at)" class="rounded border"
                          style="width: 100%; height: 100%; object-fit: cover;" @error="onIconError" />
                      </div>
                    </td>
                    <td>
                      {{ brand.title }}
                      <div class="actionBTN btn-group btn-group-sm float-end">
                        <div v-if="brand.deleted_at">
                          <button class="btn text-warning btn-sm" @click="openDeleteModal(brand, 'restore')"
                            title="بازیافت">
                            <i class="fa fa-refresh"></i>
                          </button>
                          <button class="btn text-danger btn-sm" @click="openDeleteModal(brand, 'delete')"
                            title="حذف برای همیشه">
                            <i class="fa fa-times"></i>
                          </button>
                        </div>
                        <div v-else>
                          <button class="btn text-primary btn-sm" @click="editbrand(brand)" title="ویرایش">
                            <i class="fa fa-pencil"></i>
                          </button>
                          <button class="btn text-danger btn-sm" @click="openDeleteModal(brand)" title="حذف">
                            <i class="fa fa-times"></i>
                          </button>
                        </div>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="brands.length === 0">
                    <td colspan="3" class="text-center py-4 text-muted">
                      <i class="fa fa-folder-open fa-2x mb-2"></i>
                      <div>هیچ برندی یافت نشد</div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer" v-if="totalItems > itemsPerPage">
              <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0">
                  <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <a class="page-link" href="javascript:;" @click="getData(currentPage - 1, getSearchParams())"
                      v-if="currentPage > 1">قبلی</a>
                  </li>
                  <li class="page-item" :class="{ active: page === currentPage }" v-for="page in totalPages"
                    :key="page">
                    <a class="page-link" href="javascript:;" @click="getData(page, getSearchParams())">{{ page }}</a>
                  </li>
                  <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <a class="page-link" href="javascript:;" @click="getData(currentPage + 1, getSearchParams())"
                      v-if="currentPage < totalPages">بعدی</a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- brand Modal (Create/Edit/View) -->
    <div class="modal fade" :class="{ show: showbrandModal }"
      :style="{ display: showbrandModal ? 'block' : 'none' }" tabindex="-1">
      <div class="shadow" @click="showbrandModal = false; /*currentbrand = null*/"></div>
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">
              <span v-if="modalMode === 'create'">افزودن برند جدید</span>
              <span v-else-if="modalMode === 'edit'">ویرایش برند</span>
              <span v-else-if="modalMode === 'view'">مشاهده برند</span>
            </h5>
            <button type="button" class="btn-close" @click="showbrandModal = false; currentbrand = null"></button>
          </div>
          <div class="modal-body">
            <div class="card-title">
              <div v-if="formError" class="alert alert-danger">{{ formError }}</div>
            </div>

            <form @submit.prevent="modalMode !== 'view' ? savebrand() : null" v-if="currentbrand">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">نام برند *</label>
                  <input type="text" class="form-control" v-model="currentbrand.title"
                    :readonly="modalMode === 'view'" ref="brandTitleInput" required />
                </div>
                <div class="col-12" v-if="modalMode !== 'view'">
                  <label class="form-label">آیکون برند</label>
                  <input type="file" class="form-control" ref="brandIconInput" accept="image/*,.svg"
                    @change="handleIconUpload" />
                  <div class="form-text">
                    فرمتهای مجاز: JPG, PNG, GIF, SVG, WebP
                  </div>
                  <div v-if="currentbrand.icon" class="mt-2">
                    <small class="text-success">
                      <i class="fa fa-check-circle me-1"></i>
                      فایل آیکون انتخاب شده: {{ selectedFileName }}
                    </small>
                  </div>
                </div>
                <div class="col-12" v-if="modalMode === 'view'">
                  <label class="form-label">آیکون برند</label>
                  <div class="border rounded p-2 bg-light">
                    <img :src="getBrandIconUrl(currentbrand.id, currentbrand.updated_at)" class="img-thumbnail"
                      style="max-width: 100px; max-height: 100px;" @error="onIconError" />
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer" v-if="formloading">
            درحال بارگذاری ....
            <div class="spinner-border btn btn-secondary" role="status"></div>
          </div>
          <div class="modal-footer" v-else-if="modalMode !== 'view'">
            <button type="button" class="btn btn-secondary"
              @click="showbrandModal = false; currentbrand = null">انصراف</button>
            <button type="button" class="btn btn-primary" @click="savebrand">
              <span v-if="modalMode === 'create'">افزودن برند</span>
              <span v-else-if="modalMode === 'edit'">به\u200Cروزرسانی برند</span>
            </button>
          </div>
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
  title: 'برندها'
})
const { $api } = useNuxtApp()

const config = useRuntimeConfig()
const baseUrl = config.public.apiBase


// داده\u200Cهای برند
const brands = ref([])

// وضعیت بارگذاری
const loading = ref(false)

// خطا
const error = ref(null)

// pagination
const totalItems = ref(0)
const totalPages = ref(0)
const currentPage = ref(1)

const itemsPerPage = ref(10)

// جستجو
const searchQuery = ref({
  title: '',
  status: '1'
})

// انتخاب سطرها
const selectedTr = ref([])

// شاخص سطر فعلی
const currentRowIndex = ref(-1)

const formloading = ref(false)
const formError = ref(null)

const showbrandModal = ref(false)
const modalMode = ref('create') /* 'create', 'edit', 'view' */
const currentbrand = ref(null)
const brandTitleInput = ref(null)
const brandIconInput = ref(null)
const selectedFileName = ref('')

// تابع بارگذاری داده\u200Cها
const getData = async (page = 1, searchParams = {}) => {
  loading.value = true
  error.value = null

  try {
    let url = `/products/brands?limit=${itemsPerPage.value}&page=${page}`
    if (searchParams.title && searchParams.title.trim()) url += `&title=${encodeURIComponent(searchParams.title)}`
    if (searchParams.status) url += `&status=${encodeURIComponent(searchParams.status)}`

    const response = await $api(url)
    console.log('API Response:', response)

    // اطمینان از اینکه داده\u200Cها همیشه آرایه هستند
    const data = response?.data || {}
    brands.value = Array.isArray(data.items) ? data.items : []
    totalItems.value = data.total || 0
    totalPages.value = data.last_page || 0
    currentPage.value = data.current_page || 1

    selectedTr.value = []
    currentRowIndex.value = -1 // هیچ سطری انتخاب نشده

  } catch (err) {
    console.error('Error loading brands:', err)
    console.error('Error details:', err.response?.data || err.message)
    error.value = 'خطا در بارگذاری لیست برندها'
    // در صورت خطا، آرایه‌ها را خالی کنیم
    brands.value = []
    totalItems.value = 0
    totalPages.value = 0
    currentPage.value = 1
  } finally {
    loading.value = false
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
  const totalRows = brands.value.length
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

const toggleCurrentRowSelection = () => {
  if (currentRowIndex.value === -1 || !brands.value[currentRowIndex.value]) return

  const brandId = brands.value[currentRowIndex.value].id
  toggleRowSelection(selectedTr, brandId)
}

const performCurrentRowAction = () => {
  if (currentRowIndex.value === -1 || !brands.value[currentRowIndex.value]) return

  const brand = brands.value[currentRowIndex.value]
  editbrand(brand)
}

const newbrand = reactive({
  title: ''
})


onMounted(() => {
  getData() // بارگذاری برندها
  setupKeyboardNavigation()
})

// حذف گروهی
const bulkDeleteSelected = async () => {
  if (selectedTr.value.length === 0) return

  const result = await Swal.fire({
    title: 'تأیید حذف گروهی',
    text: `آیا مطمئن هستید که می‌خواهید ${selectedTr.value.length} برند انتخاب شده را حذف کنید؟`,
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

  await performBulkDelete(selectedTr, brands, () => getData())
}

// تابع کمکی برای حذف گروهی
const performBulkDelete = async (selectedItems, brandsArray, refreshCallback) => {
  formloading.value = true
  let successCount = 0
  let errorCount = 0

  for (const brandId of selectedItems.value) {
    try {
      await $api(`/products/brands/${brandId}`, {
        method: 'DELETE'
      })
      successCount++
    } catch (err) {
      console.error(`Error deleting brand ${brandId}:`, err)
      errorCount++
    }
  }

  // پاک کردن لیست انتخاب شده
  selectedItems.value = []

  // به‌روزرسانی لیست
  if (refreshCallback) {
    await refreshCallback()
  }

  // نمایش نتیجه
  if (errorCount === 0) {
    await Swal.fire({
      title: 'انجام شد!',
      text: `${successCount} برند با موفقیت حذف شد.`,
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
      text: `${successCount} برند حذف شد. ${errorCount} برند با خطا مواجه شد.`,
      icon: 'warning',
      customClass: {
        popup: 'swal-rtl'
      }
    })
  }

  formloading.value = false
}

// تابع عمومی برای انتخاب سطرها
const toggleRowSelection = (selectedItems, brandId) => {
  const index = selectedItems.value.indexOf(brandId)
  if (index > -1) {
    selectedItems.value.splice(index, 1)
  } else {
    selectedItems.value.push(brandId)
  }
}

// تابع انتخاب سطر
const toggleRowSelectionById = (brandId) => {
  toggleRowSelection(selectedTr, brandId)
}

const getRowClass = (brandId, index) => {
  const classes = []
  if (selectedTr.value.includes(brandId)) classes.push('selected')
  if (index === currentRowIndex.value) classes.push('current-row')
  return classes.join(' ')
}

const setCurrentRow = (rowIndex) => {
  currentRowIndex.value = rowIndex
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  let dateinit = new Date(dateString)
  let date = dateinit.toLocaleDateString('fa-IR')
  let time = dateinit.toLocaleTimeString('fa-IR')
  return `${date} <span class="text-muted">${time}</span>`
}

const searchBrands = () => {
  const params = {
    title: searchQuery.value.title,
    status: searchQuery.value.status
  }
  getData(currentPage.value, params)
}

const resetFilters = () => {
  searchQuery.value = {
    title: '',
    status: '1'
  }
  selectedTr.value = []
  currentRowIndex.value = -1
  getData(currentPage.value)
}

// تابع برای گرفتن پارامترهای جستجوی فعلی
const getSearchParams = () => {
  if (searchQuery.value && (searchQuery.value.title || searchQuery.value.status)) {
    return {
      title: searchQuery.value.title,
      status: searchQuery.value.status
    }
  }
  return {}
}

// تابع مدیریت آپلود آیکون
const handleIconUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    // بررسی اندازه فایل (حداکثر 2MB)
    if (file.size > 2 * 1024 * 1024) {
      formError.value = 'حجم فایل آیکون نباید بیشتر از ۲ مگابایت باشد'
      event.target.value = ''
      return
    }

    // بررسی فرمت فایل
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']
    if (!allowedTypes.includes(file.type) && !file.name.toLowerCase().endsWith('.svg')) {
      formError.value = 'فرمت فایل مجاز نیست. لطفا از فرمت\u200Cهای JPG, PNG, GIF, WebP یا SVG استفاده کنید'
      event.target.value = ''
      return
    }

    selectedFileName.value = file.name
    currentbrand.value.icon = file
    formError.value = null
  }
}

// تابع بررسی اینکه فایل تصویر است یا نه
const isImageFile = (file) => {
  if (typeof file === 'string') {
    return file.match(/\.(jpg|jpeg|png|gif|webp)$/i)
  }
  return file && file.type && file.type.startsWith('image/')
}

// تابع گرفتن نام فایل از URL یا File
const getFileName = (file) => {
  if (typeof file === 'string') {
    return file.split('/').pop()
  }
  return file && file.name ? file.name : 'فایل نامشخص'
}

// تابع ساخت URL آیکون برند
const getBrandIconUrl = (brandId, updated_at = 0) => {
  return `${baseUrl}storage/products/brands/${brandId}?v=${updated_at}`
}

// تابع مدیریت خطای بارگذاری آیکون
const onIconError = (event) => {
  event.target.style.display = 'none'
  event.target.nextElementSibling?.remove()
  const parent = event.target.parentElement
  const placeholder = document.createElement('div')
  placeholder.className = 'd-flex align-items-center justify-content-center h-100 text-muted'
  placeholder.innerHTML = '<i class="fa fa-image" style="font-size: 16px;"></i>'
  parent.appendChild(placeholder)
}


const savebrand = async () => {
  if (!currentbrand.value.title.trim()) {
    formError.value = 'عنوان برند الزامی است'
    return
  }

  formloading.value = true
  formError.value = null

  try {
    // استفاده از FormData برای ارسال فایل
    const formData = new FormData()
    formData.append('title', currentbrand.value.title)

    // اضافه کردن فایل آیکون اگر انتخاب شده باشد
    if (currentbrand.value.icon && currentbrand.value.icon instanceof File) {
      formData.append('icon', currentbrand.value.icon)
    }

    let response
    if (modalMode.value === 'create') {
      response = await $api('/products/brands', {
        method: 'POST',
        body: formData
      })

      if (response?.data) {
        const newBrand = response.data
        brands.value.unshift(newBrand)
      }
    } else if (modalMode.value === 'edit') {
      response = await $api(`/products/brands/${currentbrand.value.id}`, {
        method: 'POST', // تغییر به POST برای FormData
        body: formData,
        query: { _method: 'PUT' } // برای ارسال PUT از طریق POST
      })
      console.log(response)

      if (response?.data) {
        const updatedBrand = response.data

        if (response?.data) {
          const index = brands.value.findIndex(brand => brand.id === updatedBrand.id)
          if (index !== -1) {
            response.data.updated_at = new Date().toISOString()
            brands.value[index] = response.data
          }
        }

      }
    }

    showbrandModal.value = false
    currentbrand.value = null

  } catch (err) {
    const status = err?.response?.status
    const data = err?.response?._data

    if (status === 422 && data?.errors) {
      formError.value = Object.values(data.errors)
        .flat()
        .join(' ، ')
    }
    else if (data?.message) {
      formError.value = "خطایی رخ داده لطفا با پشتیبانی تماس بگیرید"
    }
    else {
      formError.value = 'خطایی در ارتباط با سرور رخ داد'
    }
  } finally {
    formloading.value = false
  }
}


const openbrandModal = async (mode = 'create', brand = null) => {
  modalMode.value = mode
  formError.value = null
  selectedFileName.value = ''

  if (mode === 'create') {
    currentbrand.value = {
      title: '',
      icon: null
    }
  } else if (brand) {
    currentbrand.value = { ...brand }
  }

  showbrandModal.value = true

  await nextTick()
  if (brandTitleInput.value) {
    brandTitleInput.value.focus()
  }
}

const editbrand = (brand) => {
  openbrandModal('edit', brand)
}

const viewbrand = (brand) => {
  openbrandModal('view', brand)
}



const openDeleteModal = async (brand, method = '') => {
  let title, text, icon, confirmButtonText, confirmButtonColor

  if (method === 'restore') {
    title = 'بازیابی برند'
    text = `آیا می\u200Cخواهید برند "${brand.title}" را بازیابی کنید؟`
    icon = 'question'
    confirmButtonText = 'بله، بازیابی کن'
    confirmButtonColor = '#28a745'
  } else if (method === 'delete') {
    title = 'حذف کامل برند'
    text = `آیا مطمئن هستید که می\u200Cخواهید برند "${brand.title}" را برای همیشه حذف کنید؟ این عمل قابل برگشت نیست!`
    icon = 'error'
    confirmButtonText = 'بله، برای همیشه حذف کن'
    confirmButtonColor = '#dc3545'
  } else {
    title = 'حذف برند'
    text = `آیا می\u200Cخواهید برند "${brand.title}" را حذف کنید؟`
    icon = 'warning'
    confirmButtonText = 'بله، حذف کن'
    confirmButtonColor = '#dc3545'
  }

  const result = await Swal.fire({
    title,
    text,
    icon,
    showCancelButton: true,
    confirmButtonColor,
    cancelButtonColor: '#6c757d',
    confirmButtonText,
    cancelButtonText: 'لغو',
    reverseButtons: true,
    customClass: {
      popup: 'swal-rtl'
    }
  })

  if (result.isConfirmed) {
    await confirmDelete(brand, method)
  }
}

const confirmDelete = async (brand, method = '') => {
  formloading.value = true
  error.value = null

  try {
    let url, httpMethod, successMessage

    if (method === 'restore') {
      url = `/products/brands/${brand.id}/restore`
      httpMethod = 'PATCH'
      successMessage = 'برند با موفقیت بازیابی شد!'
    } else if (method === 'delete') {
      url = `/products/brands/${brand.id}/force`
      httpMethod = 'DELETE'
      successMessage = 'برند برای همیشه حذف شد!'
    } else {
      url = `/products/brands/${brand.id}`
      httpMethod = 'DELETE'
      successMessage = 'برند با موفقیت حذف شد!'
    }

    await $api(url, {
      method: httpMethod
    })

    // حذف از لیست
    const filtered = brands.value.filter(item => item.id !== brand.id)
    brands.value = filtered

    // نمایش پیام موفقیت
    await Swal.fire({
      title: 'انجام شد!',
      text: successMessage,
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
      customClass: {
        popup: 'swal-rtl'
      }
    })

  } catch (err) {
    console.error('Error deleting brand:', err)
    const status = err?.response?.status
    const data = err?.response?._data
    let errorMessage = 'خطا در عملیات'

    if (status === 404) {
      errorMessage = 'برند یافت نشد'
    } else if (status === 403) {
      errorMessage = 'شما دسترسی انجام این عملیات را ندارید'
    } else if (data?.message) {
      errorMessage = data.message
    }

    await Swal.fire({
      title: 'خطا!',
      text: errorMessage,
      icon: 'error',
      customClass: {
        popup: 'swal-rtl'
      }
    })
  } finally {
    formloading.value = false
  }
}

</script>
