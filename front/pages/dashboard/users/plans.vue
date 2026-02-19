<template>
  <div class="plans-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">مدیریت پلن‌ها</h4>
          <p class="text-muted mt-1">مدیریت پلن‌های اشتراکی نرم‌افزار</p>
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
          <button class="btn btn-primary" @click="openPlanModal('create')">
            <i class="fa fa-plus-circle me-1"></i>
            افزودن پلن
          </button>
        </div>
      </div>
    </div>

    <!-- جستجو -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="searchPlans" class="mb-0">
              <div class="row g-3">
                <div class="col-md-4">
                  <input type="text" class="form-control" placeholder="جستجو بر اساس عنوان..."
                    v-model="searchQuery.title" />
                </div>
                <div class="col-md-2">
                  <select class="form-select" v-model="searchQuery.status">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="1">فعال</option>
                    <option value="0">غیرفعال</option>
                    <option value="deleted">سطل زباله</option>
                  </select>
                </div>
                <div class="col-md-2" v-if="searchQuery.title || searchQuery.status">
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

    <!-- جدول پلن‌ها -->
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
                    <th width="5%">شناسه</th>
                    <th width="30%">عنوان</th>
                    <th width="40%">گزینه‌ها</th>
                    <th width="10%">وضعیت</th>
                    <th width="15%">عملیات</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(plan, index) in plans" :key="plan.id"
                    :class="getRowClass(plan.id, index)" @click="setCurrentRow(index)">
                    <td @click="toggleRowSelectionById(plan.id)">
                      <input type="checkbox" v-if="selectedTr.includes(plan.id)" checked
                        class="form-check-input me-2">
                      <a href="javascript:;" v-else class="text-decoration-none">{{ plan.id }}</a>
                    </td>
                    <td>{{ plan.title }}</td>
                    <td>
                      <pre v-if="plan.option" class="mb-0 small">{{ JSON.stringify(plan.option, null, 2) }}</pre>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td>
                      <span :class="getStatusBadgeClass(plan.status)">
                        {{ plan.status == 1 ? 'فعال' : 'غیرفعال' }}
                      </span>
                    </td>
                    <td>
                      <div class="actionBTN btn-group btn-group-sm">
                        <div v-if="plan.deleted_at">
                          <button class="btn text-warning btn-sm" @click="openDeleteModal(plan, 'restore')"
                            title="بازیافت">
                            <i class="fa fa-refresh"></i>
                          </button>
                          <button class="btn text-danger btn-sm" @click="openDeleteModal(plan, 'delete')"
                            title="حذف برای همیشه">
                            <i class="fa fa-times"></i>
                          </button>
                        </div>
                        <div v-else>
                          <button class="btn text-primary btn-sm" @click="editPlan(plan)" title="ویرایش">
                            <i class="fa fa-edit"></i>
                          </button>
                          <button class="btn text-danger btn-sm" @click="openDeleteModal(plan)" title="حذف">
                            <i class="fa fa-times"></i>
                          </button>
                        </div>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="plans.length === 0">
                    <td colspan="5" class="text-center py-4 text-muted">
                      <i class="fa fa-list fa-2x mb-2"></i>
                      <div>هیچ پلنی یافت نشد</div>
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

    <!-- Plan Modal (Create/Edit) -->
    <div class="modal fade" :class="{ show: showPlanModal }"
      :style="{ display: showPlanModal ? 'block' : 'none' }" tabindex="-1">
      <div class="shadow" @click="showPlanModal = false; currentPlan = null"></div>
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">
              <span v-if="modalMode === 'create'">افزودن پلن جدید</span>
              <span v-else-if="modalMode === 'edit'">ویرایش پلن</span>
            </h5>
            <button type="button" class="btn-close" @click="showPlanModal = false; currentPlan = null"></button>
          </div>
          <div class="modal-body">
            <div class="card-title">
              <div v-if="formError" class="alert alert-danger">{{ formError }}</div>
            </div>

            <form @submit.prevent="savePlan" v-if="currentPlan" id="planForm">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">عنوان پلن *</label>
                  <input type="text" class="form-control" v-model="currentPlan.title"
                    ref="planTitleInput" required placeholder="مثال: اشتراک طلایی" />
                </div>
                <div class="col-12">
                  <label class="form-label">گزینه‌ها (JSON)</label>
                  <textarea rows="5" class="form-control" v-model="planOptionJson"
                    placeholder='{"price": 1000000, "duration": 12, "features": ["feature1", "feature2"]}'></textarea>
                  <small class="text-muted">گزینه‌های پلن به صورت JSON (اختیاری)</small>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" v-model="currentPlan.status" :true-value="1"
                      :false-value="0" id="planStatus">
                    <label class="form-check-label" for="planStatus">
                      فعال
                    </label>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer" v-if="formloading">
            درحال بارگذاری ....
            <div class="spinner-border btn btn-secondary" role="status"></div>
          </div>
          <div class="modal-footer" v-else>
            <button type="button" class="btn btn-secondary"
              @click="showPlanModal = false; currentPlan = null">انصراف</button>
            <button type="submit" class="btn btn-primary" form="planForm" @click.prevent="savePlan">
              <span v-if="modalMode === 'create'">افزودن پلن</span>
              <span v-else-if="modalMode === 'edit'">به‌روزرسانی پلن</span>
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
  layout: 'windows',
  middleware: 'auth',
  title: 'مدیریت پلن‌ها'
})
const { $api } = useNuxtApp()

// داده‌های پلن
const plans = ref([])

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
  status: ''
})

// انتخاب سطرها
const selectedTr = ref([])

// شاخص سطر فعلی
const currentRowIndex = ref(-1)

const formloading = ref(false)
const formError = ref(null)

const showPlanModal = ref(false)
const modalMode = ref('create') /* 'create', 'edit' */
const currentPlan = ref(null)
const planTitleInput = ref(null)
const planOptionJson = ref('')

// تابع بارگذاری داده‌ها
const getData = async (page = 1, searchParams = {}) => {
  loading.value = true
  error.value = null

  try {
    const payload = {
      limit: itemsPerPage.value,
      page: page
    }
    
    if (searchParams.title && searchParams.title.trim()) payload.title = searchParams.title
    if (searchParams.status) payload.status = searchParams.status

    const response = await $api('/users/plans/list', {
      method: 'POST',
      body: payload
    })
    console.log('API Response:', response)

    const data = response?.items || {}
    plans.value = data.data || []
    totalItems.value = data.total || 0
    totalPages.value = data.last_page || 0
    currentPage.value = data.current_page || page || 1

    selectedTr.value = []
    currentRowIndex.value = -1

  } catch (err) {
    console.error('Error loading plans:', err)
    console.error('Error details:', err.response?.data || err.message)
    error.value = 'خطا در بارگذاری لیست پلن‌ها'
    plans.value = []
    totalItems.value = 0
    totalPages.value = 0
    currentPage.value = 1
  } finally {
    loading.value = false
  }
}

// تابع انتخاب سطر
const toggleRowSelectionById = (planId) => {
  const index = selectedTr.value.indexOf(planId)
  if (index > -1) {
    selectedTr.value.splice(index, 1)
  } else {
    selectedTr.value.push(planId)
  }
}

const getRowClass = (planId, index) => {
  const classes = []
  if (selectedTr.value.includes(planId)) classes.push('selected')
  if (index === currentRowIndex.value) classes.push('current-row')
  return classes.join(' ')
}

const setCurrentRow = (rowIndex) => {
  currentRowIndex.value = rowIndex
}

const getStatusBadgeClass = (status) => {
  return status == 1 ? 'badge bg-success' : 'badge bg-secondary'
}

const searchPlans = () => {
  const params = {
    title: searchQuery.value.title,
    status: searchQuery.value.status
  }
  getData(currentPage.value, params)
}

const resetFilters = () => {
  searchQuery.value = {
    title: '',
    status: ''
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

const savePlan = async () => {
  if (!currentPlan.value.title?.trim()) {
    formError.value = 'عنوان پلن الزامی است'
    return
  }

  formloading.value = true
  formError.value = null

  try {
    let optionData = {}
    if (planOptionJson.value.trim()) {
      try {
        optionData = JSON.parse(planOptionJson.value)
      } catch (e) {
        formError.value = 'فرمت JSON گزینه‌ها نامعتبر است'
        formloading.value = false
        return
      }
    }

    const payload = {
      title: currentPlan.value.title.trim(),
      option: optionData,
      status: currentPlan.value.status ?? 1,
    }

    let response
    if (modalMode.value === 'create') {
      response = await $api('/users/plans', {
        method: 'POST',
        body: payload
      })

      if (response?.data) {
        await getData()
      }
    } else if (modalMode.value === 'edit') {
      response = await $api(`/users/plans/${currentPlan.value.id}`, {
        method: 'PUT',
        body: payload
      })

      if (response?.data) {
        await getData()
      }
    }

    showPlanModal.value = false
    currentPlan.value = null
    planOptionJson.value = ''

  } catch (err) {
    const status = err?.response?.status
    const data = err?.response?._data

    if (status === 422 && data?.errors) {
      formError.value = Object.values(data.errors)
        .flat()
        .join(' ، ')
    }
    else if (data?.message) {
      formError.value = data.message
    }
    else {
      formError.value = 'خطایی در ارتباط با سرور رخ داد'
    }
  } finally {
    formloading.value = false
  }
}

const openPlanModal = async (mode = 'create', plan = null) => {
  modalMode.value = mode
  formError.value = null

  if (mode === 'create') {
    currentPlan.value = {
      title: '',
      status: 1
    }
    planOptionJson.value = ''
  } else if (plan) {
    currentPlan.value = {
      id: plan.id,
      title: plan.title,
      status: plan.status ?? 1
    }
    planOptionJson.value = plan.option ? JSON.stringify(plan.option, null, 2) : ''
  }

  showPlanModal.value = true

  await nextTick()
  if (planTitleInput.value) {
    planTitleInput.value.focus()
  }
}

const editPlan = (plan) => {
  openPlanModal('edit', plan)
}

// حذف گروهی
const bulkDeleteSelected = async () => {
  if (selectedTr.value.length === 0) return

  const result = await Swal.fire({
    title: 'تأیید حذف گروهی',
    text: `آیا مطمئن هستید که می‌خواهید ${selectedTr.value.length} پلن انتخاب شده را حذف کنید؟`,
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

  formloading.value = true
  let successCount = 0
  let errorCount = 0

  for (const planId of selectedTr.value) {
    try {
      await $api(`/users/plans/${planId}`, {
        method: 'DELETE'
      })
      successCount++
    } catch (err) {
      console.error(`Error deleting plan ${planId}:`, err)
      errorCount++
    }
  }

  selectedTr.value = []
  await getData()

  if (errorCount === 0) {
    await Swal.fire({
      title: 'انجام شد!',
      text: `${successCount} پلن با موفقیت حذف شد.`,
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
      text: `${successCount} پلن حذف شد. ${errorCount} پلن با خطا مواجه شد.`,
      icon: 'warning',
      customClass: {
        popup: 'swal-rtl'
      }
    })
  }

  formloading.value = false
}

const openDeleteModal = async (plan, method = '') => {
  let title, text, icon, confirmButtonText, confirmButtonColor

  if (method === 'restore') {
    title = 'بازیابی پلن'
    text = `آیا می‌خواهید پلن "${plan.title}" را بازیابی کنید؟`
    icon = 'question'
    confirmButtonText = 'بله، بازیابی کن'
    confirmButtonColor = '#28a745'
  } else if (method === 'delete') {
    title = 'حذف کامل پلن'
    text = `آیا مطمئن هستید که می‌خواهید پلن "${plan.title}" را برای همیشه حذف کنید؟ این عمل قابل برگشت نیست!`
    icon = 'error'
    confirmButtonText = 'بله، برای همیشه حذف کن'
    confirmButtonColor = '#dc3545'
  } else {
    title = 'حذف پلن'
    text = `آیا می‌خواهید پلن "${plan.title}" را حذف کنید؟`
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
    await confirmDelete(plan, method)
  }
}

const confirmDelete = async (plan, method = '') => {
  formloading.value = true
  error.value = null

  try {
    let url, httpMethod

    if (method === 'restore') {
      url = `/users/plans/${plan.id}/restore`
      httpMethod = 'PATCH'
    } else if (method === 'delete') {
      url = `/users/plans/${plan.id}/force`
      httpMethod = 'DELETE'
    } else {
      url = `/users/plans/${plan.id}`
      httpMethod = 'DELETE'
    }

    await $api(url, {
      method: httpMethod
    })

    await getData()

  } catch (err) {
    console.error('Error deleting plan:', err)
    const status = err?.response?.status
    const data = err?.response?._data
    let errorMessage = 'خطا در عملیات'

    if (status === 404) {
      errorMessage = 'پلن یافت نشد'
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

onMounted(() => {
  getData()
})

</script>

<style scoped>
.plans-page .selected {
  background-color: #e3f2fd !important;
}

.plans-page .current-row {
  background-color: #fff3cd !important;
}

.actionBTN {
  white-space: nowrap;
}

pre {
  font-size: 0.75rem;
  max-height: 100px;
  overflow-y: auto;
}
</style>

