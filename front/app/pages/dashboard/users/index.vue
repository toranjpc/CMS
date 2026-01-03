<template>
  <div class="user-roles-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">مدیریت کاربران</h1>
          <p class="text-muted mt-1">مدیریت و مشاهده لیست کاربران سیستم</p>
          <div v-if="selectedTr.length > 0" class="mt-2">
            <small class="text-primary">
              <i class="fa fa-check-circle me-1"></i>
              {{ selectedTr.length }} سطر انتخاب شده
            </small>
          </div>
        </div>
        <div class="d-flex gap-2">
          <button v-if="searchQuery.status != 'deleted' && selectedTr.length > 0" class="btn btn-outline-danger"
            @click="bulkDeleteSelected" title="حذف گروهی">
            <i class="fa fa-trash me-1"></i>
            حذف انتخاب شده‌ها ({{ selectedTr.length }})
          </button>
          <button class="btn btn-primary" @click="openUserModal('create')">
            <i class="fa fa-plus-circle me-1"></i>
            افزودن کاربر
          </button>
        </div>
      </div>
    </div>

    <!-- Search and Filter -->
    <!-- Search and Filter -->
    <div class="card mb-4">
      <div class="card-body">
        <form @submit.prevent="searchFilters">
          <div class="row g-3">
            <div class="col-md-2">
              <input type="text" class="form-control" placeholder="نام..." v-model="searchQuery.name" />
            </div>
            <div class="col-md-2">
              <input type="text" class="form-control" placeholder="نام خانوادگی..." v-model="searchQuery.lastname" />
            </div>
            <div class="col-md-2">
              <input type="text" class="form-control" placeholder="نام کاربری..." v-model="searchQuery.username" />
            </div>
            <div class="col-md-2">
              <input type="text" class="form-control" placeholder="موبایل..." v-model="searchQuery.mobile" />
            </div>
            <div class="col-md-1">
              <select class="form-select" v-model="searchQuery.sex">
                <option value="">جنسیت</option>
                <option value="men">مرد</option>
                <option value="women">زن</option>
              </select>
            </div>
            <div class="col-md-2">
              <select class="form-select" v-model="searchQuery.status">
                <option value="1">لیست فعال</option>
                <option value="deleted">سطل زباله</option>
              </select>
            </div>
            <div class="col-md-1" v-if="hasActiveFilters">
              <button type="submit" class="btn border-success text-success">
                <i class="fa fa-search"></i>
              </button>
              <button type="button" class="btn border-warning text-warning" @click="resetFilters">
                <i class="fa fa-times"></i>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card">
      <div class="card-body">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
          </div>
          <div class="mt-2 text-muted">در حال بارگذاری لیست کاربران...</div>
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

        <!-- Users Table -->
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th width="8%">شناسه <small class="text-muted">(کلیک برای انتخاب)</small></th>
                <th width="">نام و نام خانوادگی</th>
                <th width="8%">نام کاربری</th>
                <th width="10%">موبایل</th>
                <th width="6%">کد ملی</th>
                <th width="6%">نوع</th>
                <th width="8%">نقش</th>
                <th width="10%">تاریخ ایجاد</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(user, index) in users" :key="user.id" :class="getRowClass(user.id, index)"
                @click="setCurrentRow(index)">
                <td @click="toggleRowSelection(user.id)">
                  <input type="checkbox" v-if="selectedTr.includes(user.id)" checked class="form-check-input me-2">
                  <a href="javascript:;" v-else class="text-decoration-none">{{ user.id }}</a>

                  <div class="avatar me-3">
                    <img :src="getUserAvatarUrl(user)" :alt="user.name" class="rounded-circle" width="40" height="40" />
                  </div>

                </td>
                <td>
                  <div class="fw-semibold">{{ user.name }} {{ user.lastname }}</div>

                  <div class="actionBTN btn-group btn-group-sm float-end">
                    <div v-if="user.deleted_at">
                      <button class="btn text-warning" @click="openDeleteModal(user, 'restore')" title="بازیافت">
                        <i class="fa fa-refresh"></i>
                      </button>
                      <button class="btn text-danger" @click="openDeleteModal(user, 'delete')" title="حذف برای همیشه">
                        <i class="fa fa-times"></i>
                      </button>
                    </div>
                    <div v-else>
                      <button class="btn text-primary" @click="editUser(user)" title="ویرایش">
                        <i class="fa fa-pencil"></i>
                      </button>
                      <button class="btn text-info" @click="viewUser(user)" title="مشاهده">
                        <i class="fa fa-eye"></i>
                      </button>
                      <button class="btn text-danger" @click="openDeleteModal(user)" title="حذف">
                        <i class="fa fa-times"></i>
                      </button>
                    </div>
                  </div>
                </td>
                <td>{{ user.username }}</td>
                <td>{{ user.mobile }}</td>
                <td>{{ user.national_code || '-' }}</td>
                <td>
                  <span class="badge" :class="getTypeBadgeClass(user.type)">
                    {{ getTypeLabel(user.type) }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-secondary">
                    {{ getJobLabel(user.job) }}
                  </span>
                </td>
                <td>
                  <small class="" dir="ltr" v-html="formatDate(user.created_at)"></small>
                </td>
              </tr>
              <!-- Empty State -->
              <tr v-if="totalitems === 0">
                <td colspan="8" class="text-center py-5">
                  <div class="text-muted">
                    <i class="fa fa-users display-4 mb-3"></i>
                    <div>هیچ کاربری یافت نشد</div>
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
              totalitems) }} از {{ totalitems }} کاربر

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

    <!-- User Modal (Create/Edit/View) -->
    <div class="modal fade" :class="{ show: showUserModal }" :style="{ display: showUserModal ? 'block' : 'none' }"
      tabindex="-1">
      <div class="shadow"
        @click="showUserModal = false; currentUser = null; avatarInput?.value && (avatarInput.value.value = '')"></div>
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">
              <span v-if="modalMode === 'create'">افزودن کاربر جدید</span>
              <span v-else-if="modalMode === 'edit'">ویرایش کاربر</span>
              <span v-else-if="modalMode === 'view'">مشاهده کاربر</span>
            </h5>
            <button type="button" class="btn-close"
              @click="showUserModal = false; currentUser = null; avatarInput?.value && (avatarInput.value.value = '')"></button>
          </div>
          <div class="modal-body">
            <div class="card-title">
              <div v-if="formError" class="alert alert-danger">{{ formError }}</div>
            </div>

            <form @submit.prevent="saveUser" v-if="currentUser" enctype='multipart/form-data' id="userForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">نام *</label>
                  <input type="text" autocomplete="OFF" class="form-control" v-model="currentUser.name"
                    :readonly="modalMode === 'view'" ref="userNameInput" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">نام خانوادگی *</label>
                  <input type="text" autocomplete="OFF" class="form-control" v-model="currentUser.lastname"
                    :readonly="modalMode === 'view'" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">نام کاربری *</label>
                  <input type="text" autocomplete="OFF" class="form-control" v-model="currentUser.username"
                    :readonly="modalMode === 'view'" :disabled="modalMode === 'edit'" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">موبایل</label>
                  <input type="text" autocomplete="OFF" class="form-control" v-model="currentUser.mobile"
                    :readonly="modalMode === 'view'" />
                </div>
                <div class="col-md-6" v-if="modalMode !== 'view'">
                  <label class="form-label">رمز عبور</label>
                  <input type="password" autocomplete="OFF" class="form-control" v-model="currentUser.password" />
                  <small class="text-muted" v-if="modalMode === 'edit'">در صورت عدم نیاز به تغییر رمز عبور، خالی
                    بگذارید</small>
                </div>
                <div class="col-md-6" v-if="modalMode !== 'view'">
                  <label class="form-label">تکرار رمز عبور</label>
                  <input type="password" autocomplete="OFF" class="form-control"
                    v-model="currentUser.password_confirmation" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">جنسیت</label>
                  <select class="form-select" v-model="currentUser.sex" :disabled="modalMode === 'view'">
                    <option value="1">مرد</option>
                    <option value="0">زن</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">نقش</label>
                  <select class="form-select" v-model="currentUser.job" :disabled="modalMode === 'view'">
                    <option v-for="role in availableRoles" :key="role.id" :value="role.id">
                      {{ role.title }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">نوع کاربری</label>
                  <select class="form-select" v-model="currentUser.type" :disabled="modalMode === 'view'">
                    <option value="user">کاربر</option>
                    <option value="seller">فروشنده</option>
                    <option value="staff">پرسنل</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">شناسه معرف</label>
                  <input type="text" autocomplete="OFF" class="form-control" v-model="currentUser.referral_id"
                    :readonly="modalMode === 'view'" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">کد ملی</label>
                  <input type="text" autocomplete="OFF" class="form-control" v-model="currentUser.national_code"
                    :readonly="modalMode === 'view'" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">تاریخ تولد</label>
                  <input type="text" autocomplete="OFF" class="form-control"
                    :value="formatPersianDate(currentUser.birth_date)"
                    :readonly="modalMode === 'view'" placeholder="مثال: ۱۳۷۰/۰۱/۰۱" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">کد پستی</label>
                  <input type="text" autocomplete="OFF" class="form-control" v-model="currentUser.ircode"
                    :readonly="modalMode === 'view'" />
                </div>
                <div class="col-12" v-if="modalMode !== 'view'">
                  <label class="form-label">آواتار</label>
                  <input type="file" autocomplete="OFF" class="form-control" @change="handleAvatarChange"
                    accept="image/*" ref="avatarInput" :readonly="modalMode === 'view'" />
                  <small class="text-muted">فرمت‌های مجاز: JPG, PNG, GIF</small>
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
              @click="showUserModal = false; currentUser = null; avatarInput?.value && (avatarInput.value.value = '')">انصراف</button>
            <button type="submit" class="btn btn-primary" form="userForm">
              <span v-if="modalMode === 'create'">افزودن کاربر</span>
              <span v-else-if="modalMode === 'edit'">به‌روزرسانی کاربر</span>
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
  title: 'مدیریت کاربران'
})
const config = useRuntimeConfig()
const baseUrl = config.public.apiBase
const { $api } = useNuxtApp()

const users = ref([])
const loading = ref(false)
const formloading = ref(false)
const formError = ref(null)
const error = ref(null)
const totalitems = ref(0)
const totalPages = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(10)
const searchQuery = ref({
  name: '',
  lastname: '',
  username: '',
  mobile: '',
  sex: '',
  status: '1'
})
const selectedTr = ref([])
const currentRowIndex = ref(-1)

const showUserModal = ref(false)
const modalMode = ref('create') /* 'create', 'edit', 'view' */
const currentUser = ref(null)
const userNameInput = ref(null)
const avatarInput = ref(null)
const availableRoles = ref([])

const getData = async (iPP, cP, wP = 0, searchParams = {}) => {
  loading.value = true
  error.value = null

  try {
    let url = `/users?limit=${iPP}&page=${cP}`

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

    users.value = response.data?.items || []
    totalitems.value = response.data?.total || 0
    totalPages.value = response.data?.last_page || 0
    currentPage.value = response.data?.current_page || 1

    // ریست کردن انتخاب‌ها وقتی داده‌ها تغییر می‌کنند
    selectedTr.value = []
    currentRowIndex.value = -1 // هیچ سطری انتخاب نشده

    // اگر نقش‌ها هنوز بارگذاری نشده‌اند، بارگذاری کنیم
    if (availableRoles.value.length === 0 && wP === 1) {
      await loadRoles()
    }

  } catch (err) {
    error.value = 'خطا در بارگذاری لیست کاربران'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  getData(itemsPerPage.value, currentPage.value, 1)
  setupKeyboardNavigation()
})

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
        // برای users.vue، کلیدهای چپ و راست کاری انجام نمی‌دهند
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
  const totalRows = users.value.length
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
  if (currentRowIndex.value === -1 || !users.value[currentRowIndex.value]) return

  const userId = users.value[currentRowIndex.value].id
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
    text: `آیا مطمئن هستید که می‌خواهید ${selectedTr.value.length} کاربر انتخاب شده را حذف کنید؟`,
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

  for (const userId of selectedTr.value) {
    try {
      await $api(`/users/${userId}`, {
        method: 'DELETE'
      })
      successCount++
    } catch (err) {
      errorCount++
    }
  }

  selectedTr.value = []
  currentRowIndex.value = -1

  await getData(itemsPerPage.value, currentPage.value, 0)

  if (errorCount === 0) {
    await Swal.fire({
      title: 'انجام شد!',
      text: `${successCount} کاربر با موفقیت حذف شد.`,
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
      text: `${successCount} کاربر حذف شد. ${errorCount} کاربر با خطا مواجه شد.`,
      icon: 'warning',
      customClass: {
        popup: 'swal-rtl'
      }
    })
  }

  formloading.value = false
}

const performCurrentRowAction = () => {
  if (currentRowIndex.value === -1 || !users.value[currentRowIndex.value]) return

  const selectedUser = users.value[currentRowIndex.value]
  editUser(selectedUser)
}

const loadRoles = async () => {
  try {
    const response = await $api('/users/jobs')
    availableRoles.value = response.data?.items || []
  } catch (err) {
    console.error('Error loading roles:', err)
  }
}

const getJobLabel = (jobId) => {
  const role = availableRoles.value.find(r => r.id == jobId)
  return role ? role.title : `نقش ${jobId}`
}

const getTypeLabel = (type) => {
  const labels = {
    user: 'کاربر',
    seller: 'فروشنده',
    staff: 'پرسنل'
  }
  return labels[type] || type
}

const getTypeBadgeClass = (type) => {
  const classes = {
    user: 'bg-primary',
    seller: 'bg-success',
    staff: 'bg-warning'
  }
  return classes[type] || 'bg-secondary'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  let dateinit = new Date(dateString)
  let date = dateinit.toLocaleDateString('fa-IR')
  let time = dateinit.toLocaleTimeString('fa-IR')
  return `${date} <span class="text-muted">${time}</span>`
}

// تبدیل تاریخ میلادی به فارسی برای نمایش
const formatPersianDate = (dateString=null) => {
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
  if (Object.keys(searchQuery.value).length) getData(itemsPerPage.value, currentPage.value, 0)
}

const resetFilters = () => {
  searchQuery.value = {
    name: '',
    lastname: '',
    username: '',
    mobile: '',
    sex: '',
    status: '1'
  }
  selectedTr.value = []
  currentRowIndex.value = -1
  getData(itemsPerPage.value, currentPage.value, 0)
}

// تابع برای گرفتن پارامترهای جستجوی فعلی
const getSearchParams = () => {
  const params = {}
  if (searchQuery.value.name) params.name = searchQuery.value.name
  if (searchQuery.value.lastname) params.lastname = searchQuery.value.lastname
  if (searchQuery.value.username) params.username = searchQuery.value.username
  if (searchQuery.value.mobile) params.mobile = searchQuery.value.mobile
  if (searchQuery.value.sex !== '') params.sex = searchQuery.value.sex
  if (searchQuery.value.status !== '1') params.status = searchQuery.value.status
  return params
}

const saveUser = async () => {
  // اگر در حالت مشاهده هستیم، هیچ کاری نکن
  if (modalMode.value === 'view') return

  if (!currentUser.value.name?.trim() || !currentUser.value.lastname?.trim() || !currentUser.value.username?.trim()) {
    formError.value = 'نام، نام خانوادگی و نام کاربری الزامی هستند'
    return
  }

  if (modalMode.value === 'create' && !currentUser.value.password) {
    formError.value = 'رمز عبور الزامی است'
    return
  }

  if (currentUser.value.password && currentUser.value.password !== currentUser.value.password_confirmation) {
    formError.value = 'رمز عبور و تکرار آن باید یکسان باشند'
    return
  }

  formloading.value = true
  formError.value = null

  try {
    // استفاده از FormData برای ارسال فایل
    const formData = new FormData()

    // اضافه کردن داده‌های متنی
    formData.append('name', currentUser.value.name)
    formData.append('lastname', currentUser.value.lastname)
    formData.append('username', currentUser.value.username)
    if (currentUser.value.mobile) formData.append('mobile', parseInt(currentUser.value.mobile))
    formData.append('sex', parseInt(currentUser.value.sex))
    formData.append('job', parseInt(currentUser.value.job))
    formData.append('type', currentUser.value.type)
    formData.append('referral_id', currentUser.value.referral_id)
    formData.append('national_code', currentUser.value.national_code)
    if (currentUser.value.birth_date) formData.append('birth_date', currentUser.value.birth_date)
    formData.append('ircode', currentUser.value.ircode ? parseInt(currentUser.value.ircode) : 0)

    // اضافه کردن رمز عبور فقط اگر وارد شده باشد
    if (currentUser.value.password) {
      formData.append('password', currentUser.value.password)
      formData.append('password_confirmation', currentUser.value.password_confirmation)
    }

    // اضافه کردن فایل اواتار اگر انتخاب شده باشد
    if (currentUser.value.avatar && currentUser.value.avatar instanceof File) {
      formData.append('avatar', currentUser.value.avatar)
    }

    let response
    if (modalMode.value === 'create') {
      response = await $api('/users', {
        method: 'POST',
        body: formData
      })
    } else if (modalMode.value === 'edit') {
      response = await $api(`/users/${currentUser.value.id}`, {
        method: 'POST', // تغییر به POST برای FormData
        body: formData,
        query: { _method: 'PUT' } // برای ارسال PUT از طریق POST
      })
    }

    console.log(response)

    showUserModal.value = false
    currentUser.value = null

    // ریست کردن input فایل اواتار
    if (avatarInput.value) {
      avatarInput.value.value = ''
    }

    // به‌روزرسانی لیست کاربران از سرور
    await getData(itemsPerPage.value, currentPage.value, 0)

  } catch (err) {
    const status = err?.response?.status
    const data = err?.response?._data

    if (status === 422 && data?.errors) {
      formError.value = Object.values(data.errors)
        .flat()
        .join(' ، ')
    } else if (data?.message) {
      formError.value = "خطایی رخ داده لطفا با پشتیبانی تماس بگیرید"
    } else {
      formError.value = 'خطایی در ارتباط با سرور رخ داد'
    }
  } finally {
    formloading.value = false
  }
}

const openUserModal = async (mode = 'create', user = null) => {
  modalMode.value = mode
  formError.value = null

  if (mode === 'create') {
    currentUser.value = {
      name: '',
      lastname: '',
      username: '',
      mobile: '',
      sex: 1,
      job: availableRoles.value.length > 0 ? availableRoles.value[0].id : 1,
      type: 'user',
      referral_id: '',
      national_code: '',
      ircode: 0,
      avatar: null,
      password: '',
      password_confirmation: ''
    }
  } else if (user) {
    currentUser.value = { ...user }
    // مطمئن شدن از اینکه همه فیلدها وجود دارند و تبدیل نوع داده‌ها
    if (!currentUser.value.ircode) currentUser.value.ircode = 0
    if (!currentUser.value.type) currentUser.value.type = 'user'
    if (!currentUser.value.referral_id) currentUser.value.referral_id = ''
    if (!currentUser.value.national_code) currentUser.value.national_code = ''
    // birth_date نیازی به مقداردهی اولیه ندارد چون در create خالی است
    currentUser.value.avatar = null // در حالت edit، avatar خالی است مگر اینکه کاربر فایل جدیدی انتخاب کند
    if (typeof currentUser.value.sex === 'string') currentUser.value.sex = parseInt(currentUser.value.sex)
    if (typeof currentUser.value.job === 'string') currentUser.value.job = parseInt(currentUser.value.job)
  }

  showUserModal.value = true

  await nextTick()
  if (userNameInput.value) {
    userNameInput.value.focus()
  }
}

const editUser = (user) => {
  openUserModal('edit', user)
}

const viewUser = (user) => {
  openUserModal('view', user)
}

const getUserAvatarUrl = (user) => {
  if (!user.id || !user.updated_at) return '/images/default-avatar.png'
  return `${baseUrl}storage/users/${user.id}?v=${new Date(user.updated_at).getTime()}`
}


// این تابع دیگر استفاده نمی‌شود - اواتار همراه فرم ارسال می‌شود

const openDeleteModal = async (user, method = '') => {
  let title, text, icon, confirmButtonText, confirmButtonColor

  if (method === 'restore') {
    title = 'بازیابی کاربر'
    text = `آیا می‌خواهید کاربر "${user.name} ${user.lastname}" را بازیابی کنید؟`
    icon = 'question'
    confirmButtonText = 'بله، بازیابی کن'
    confirmButtonColor = '#28a745'
  } else if (method === 'delete') {
    title = 'حذف کامل کاربر'
    text = `آیا مطمئن هستید که می‌خواهید کاربر "${user.name} ${user.lastname}" را برای همیشه حذف کنید؟ این عمل قابل برگشت نیست!`
    icon = 'error'
    confirmButtonText = 'بله، برای همیشه حذف کن'
    confirmButtonColor = '#dc3545'
  } else {
    title = 'حذف کاربر'
    text = `آیا می‌خواهید کاربر "${user.name} ${user.lastname}" را حذف کنید؟`
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
    await confirmDelete(user, method)
  }
}

const confirmDelete = async (user, method = '') => {
  formloading.value = true
  error.value = null

  try {
    let url, httpMethod, successMessage

    if (method === 'restore') {
      url = `/users/${user.id}/restore`
      httpMethod = 'PATCH'
      successMessage = 'کاربر با موفقیت بازیابی شد!'
    } else if (method === 'delete') {
      url = `/users/${user.id}/force`
      httpMethod = 'DELETE'
      successMessage = 'کاربر برای همیشه حذف شد!'
    } else {
      url = `/users/${user.id}`
      httpMethod = 'DELETE'
      successMessage = 'کاربر با موفقیت حذف شد!'
    }

    await $api(url, {
      method: httpMethod
    })

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

    // به‌روزرسانی لیست کاربران از سرور
    await getData(itemsPerPage.value, currentPage.value, 0)

  } catch (err) {
    const status = err?.response?.status
    const data = err?.response?._data
    let errorMessage = 'خطا در عملیات'

    if (status === 404) {
      errorMessage = 'کاربر یافت نشد'
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


// مدیریت تغییر فایل اواتار
const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (!file) {
    currentUser.value.avatar = null
    return
  }

  // اعتبارسنجی فایل
  if (!file.type.startsWith('image/')) {
    formError.value = 'فقط فایل‌های تصویر مجاز هستند'
    // ریست کردن input
    avatarInput.value.value = ''
    currentUser.value.avatar = null
    return
  }

  if (file.size > 5 * 1024 * 1024) { // 5MB
    formError.value = 'حجم فایل نباید بیشتر از ۵ مگابایت باشد'
    // ریست کردن input
    avatarInput.value.value = ''
    currentUser.value.avatar = null
    return
  }

  // ذخیره فایل در currentUser
  currentUser.value.avatar = file
  formError.value = null
}

const hasActiveFilters = computed(() => {
  return searchQuery.value.name ||
    searchQuery.value.lastname ||
    searchQuery.value.username ||
    searchQuery.value.mobile ||
    searchQuery.value.sex !== '' ||
    searchQuery.value.status !== '1'
})
</script>

<style scoped>
.user-badge {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
  color: white;
}

.user-badge-low {
  background: linear-gradient(135deg, #6c757d, #5a6268);
}

.user-badge-medium {
  background: linear-gradient(135deg, #ffc107, #e0a800);
}

.user-badge-high {
  background: linear-gradient(135deg, #dc3545, #c82333);
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

.avatar img {
  object-fit: cover;
}

.progress {
  background-color: #e9ecef;
}

.progress-bar {
  background-color: #0d6efd;
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
