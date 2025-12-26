<template>
  <div class="user-roles-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">نقش‌های کاربری</h1>
          <p class="text-muted mt-1">مدیریت نقش‌ها و سطح دسترسی‌های کاربران سیستم</p>
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
          <button class="btn btn-primary" @click="openRoleModal('create')">
            <i class="fa fa-plus-circle me-1"></i>
            افزودن نقش
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
            <div class="col-md-4">
              <input type="text" class="form-control" placeholder="جستجو بر اساس عنوان نقش..."
                v-model="searchQuery.title" />
            </div>
            <div class="col-md-3">
              <select class="form-select" v-model="searchQuery.status">
                <option value="1">لیست فعال</option>
                <option value="deleted">سطل زباله</option>
              </select>
            </div>
            <div class="col-md-2" v-if="Object.values(searchQuery).length">
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

    <!-- Roles Table -->
    <div class="card">
      <div class="card-body">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
          </div>
          <div class="mt-2 text-muted">در حال بارگذاری لیست نقش‌ها...</div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <i class="fa fa-exclamation-triangle me-2"></i>
          {{ error }}
          <button class="btn btn-sm btn-outline-danger ms-2" @click=" getData(itemsPerPage, currentPage, 1)">
            <i class="fa fa-arrow-clockwise"></i>
            تلاش مجدد
          </button>
        </div>

        <!-- Roles Table -->
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th width="10%">شناسه <small class="text-muted">(کلیک برای انتخاب)</small></th>
                <th width="">عنوان</th>
                <th width="20%">تاریخ ایجاد</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(role, index) in roles" :key="role.id" :class="getRowClass(role.id, index)"
                @click="setCurrentRow(index)">
                <td @click="toggleRowSelection(role.id)">
                  <input type="checkbox" v-if="selectedTr.includes(role.id)" checked class="form-check-input me-2">
                  <a href="javascript:;" v-else class="text-decoration-none">{{ role.id }}</a>
                </td>
                <td>{{ role.title }}

                  <div class="actionBTN btn-group btn-group-sm float-end">
                    <div v-if="role.deleted_at">
                      <button class="btn text-warning" @click="openDeleteModal(role, 'restore')" title="بازیافت">
                        <i class="fa fa-refresh"></i>
                      </button>
                      <button class="btn text-danger" @click="openDeleteModal(role, 'delete')" title="حذف برای همیشه">
                        <i class="fa fa-times"></i>
                      </button>
                    </div>
                    <div v-else>
                      <button class="btn text-primary" @click="editRole(role)" title="ویرایش">
                        <i class="fa fa-pencil"></i>
                      </button>
                      <button class="btn text-info" @click="viewRole(role)" title="مشاهده">
                        <i class="fa fa-eye"></i>
                      </button>
                      <button class="btn text-danger" @click="openDeleteModal(role)" title="حذف">
                        <i class="fa fa-times"></i>
                      </button>
                    </div>
                  </div>

                </td>
                <td>
                  <small class="" dir="ltr" v-html="formatDate(role.created_at)"></small>
                </td>
              </tr>
              <!-- Empty State -->
              <tr v-if="totalitems === 0">
                <td colspan="5" class="text-center py-5">
                  <div class="text-muted">
                    <i class="fa fa-diagram-3 display-4 mb-3"></i>
                    <div>هیچ نقشی یافت نشد</div>
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
              totalitems) }} از {{ totalitems }} نقش

          </div>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" href="javascript:;" @click="getData(itemsPerPage, currentPage - 1)"
                  v-if="currentPage > 1">قبلی</a>
              </li>

              <li class="page-item" :class="{ active: page === currentPage }" v-for="page in totalPages" :key="page">
                <a class="page-link" href="javascript:;" @click="getData(itemsPerPage, page)">{{ page }}</a>
              </li>

              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link" href="javascript:;" @click="getData(itemsPerPage, parseInt(currentPage) + 1)"
                  v-if="currentPage < totalPages">بعدی</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Role Modal (Create/Edit/View) -->
    <div class="modal fade" :class="{ show: showRoleModal }" :style="{ display: showRoleModal ? 'block' : 'none' }"
      tabindex="-1">
      <div class="shadow" @click="showRoleModal = false; currentRole = null"></div>
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">
              <span v-if="modalMode === 'create'">افزودن نقش جدید</span>
              <span v-else-if="modalMode === 'edit'">ویرایش نقش</span>
              <span v-else-if="modalMode === 'view'">مشاهده نقش</span>
            </h5>
            <button type="button" class="btn-close" @click="showRoleModal = false; currentRole = null"></button>
          </div>
          <div class="modal-body">
            <div class="card-title">
              <div v-if="formError" class="alert alert-danger">{{ formError }}</div>
            </div>

            <form @submit.prevent="modalMode !== 'view' ? saveRole() : null" v-if="currentRole">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">نام نقش *</label>
                  <input type="text" class="form-control" v-model="currentRole.title" :readonly="modalMode === 'view'"
                    ref="roleTitleInput" required />
                </div>
                <div class="col-12">
                  <label class="form-label">دسترسی‌ها</label>
                  <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                    <div v-for="category in permissionCategories" :key="category" class="mb-3">
                      <h6 class="mb-2" v-if="category !== '*'">
                        <input class="d-none" type="checkbox" :id="category"
                          :checked="isCurrentCategoryChecked(category)"
                          @change="modalMode !== 'view' ? toggleCurrentCategoryPermissions(category) : null"
                          :disabled="modalMode === 'view'" />
                        <label class="btn btn-sm"
                          :class="isCurrentCategoryChecked(category) ? 'btn-success' : 'btn-outline-secondary'"
                          :for="category" role="button">
                          {{ trans(category) }}
                        </label>
                      </h6>
                      <div class="ms-3 permition-list">
                        <div class="mb-1" v-for="permission in getPermissionsByCategory(category)"
                          :key="permission.key">
                          <input class="d-none" type="checkbox" :id="permission.key" v-model="currentRole.permissions"
                            :value="permission.key" :disabled="modalMode === 'view'" />
                          <label class="btn d-block btn-xs me-1 mb-1 text-truncate"
                            :class="currentRole.permissions.includes(permission.key) ? 'btn-primary' : 'btn-outline-primary'"
                            :for="permission.key" role="button">
                            {{ trans(permission.label) }}
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="availablePermissions.length === 0" class="text-muted small">
                    در حال بارگذاری دسترسی‌ها...
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
              @click="showRoleModal = false; currentRole = null">انصراف</button>
            <button type="button" class="btn btn-primary" @click="saveRole">
              <span v-if="modalMode === 'create'">افزودن نقش</span>
              <span v-else-if="modalMode === 'edit'">به‌روزرسانی نقش</span>
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
  title: 'نقش‌های کاربری'
})
const { $api } = useNuxtApp()

const roles = ref([])
const loading = ref(false)
const formloading = ref(false)
const formError = ref(null)
const error = ref(null)
const totalitems = ref(0)
const totalPages = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(10)
const searchQuery = ref([])
const selectedTr = ref([])
const currentRowIndex = ref(-1)

const showRoleModal = ref(false)
const modalMode = ref('create') /* 'create', 'edit', 'view' */
const currentRole = ref(null)
const roleTitleInput = ref(null)
const availablePermissions = ref([])

const getData = async (iPP, cP, wP = 0) => {
  loading.value = true
  error.value = null

  try {
    let url = `/users/jobs?withPers=${wP}&limit=${iPP}&page=${cP}`

    const keys = Object.keys(searchQuery.value)
    if (keys.length) {
      const queryString = keys
        .filter(key => searchQuery.value[key] !== '' && searchQuery.value[key] !== null && searchQuery.value[key] !== undefined)
        .map(key => `${key}=${encodeURIComponent(searchQuery.value[key])}`)
        .join('&')
      url += "&" + queryString
    }


    const response = await $api(url)

    roles.value = response.data?.items || []
    totalitems.value = response.data?.total || 0
    totalPages.value = response.data?.last_page || 0
    currentPage.value = response.data?.current_page || 1

    // ریست کردن انتخاب‌ها وقتی داده‌ها تغییر می‌کنند
    selectedTr.value = []
    currentRowIndex.value = -1 // هیچ سطری انتخاب نشده

    if (response.pers.length) {
      availablePermissions.value = (response.pers || []).map(perm => ({
        key: perm,
        label: perm,
        category: perm.split('.')[0]
      }))
    }

  } catch (err) {
    error.value = 'خطا در بارگذاری لیست نقش‌ها'
  } finally {
    loading.value = false
  }
}


const newRole = reactive({
  title: '',
  permissions: []
})


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
        // برای roles.vue، کلیدهای چپ و راست کاری انجام نمی‌دهند
        break
      case ' ':
        event.preventDefault()
        toggleCurrentRowSelection()
        break
      case 'Enter':
        event.preventDefault()
        // Enter روی سطر فعلی کلیک می‌کند
        if (currentRowIndex.value >= 0 && roles.value[currentRowIndex.value]) {
          // می‌توانیم اینجا عملیات خاصی تعریف کنیم، مثلاً ویرایش
        }
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
  const totalRows = roles.value.length
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
  if (currentRowIndex.value === -1 || !roles.value[currentRowIndex.value]) return

  const roleId = roles.value[currentRowIndex.value].id
  toggleRowSelection(roleId)
}

const toggleRowSelection = (roleId) => {
  const index = selectedTr.value.indexOf(roleId)
  if (index > -1) {
    // حذف از لیست انتخاب شده
    selectedTr.value.splice(index, 1)
  } else {
    // اضافه کردن به لیست انتخاب شده
    selectedTr.value.push(roleId)
  }
}

const getRowClass = (roleId, index) => {
  const classes = []

  if (selectedTr.value.includes(roleId)) {
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
    text: `آیا مطمئن هستید که می‌خواهید ${selectedTr.value.length} نقش انتخاب شده را حذف کنید؟`,
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

  for (const roleId of selectedTr.value) {
    try {
      await $api(`/users/jobs/${roleId}`, {
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
      text: `${successCount} نقش با موفقیت حذف شد.`,
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
      text: `${successCount} نقش حذف شد. ${errorCount} نقش با خطا مواجه شد.`,
      icon: 'warning',
      customClass: {
        popup: 'swal-rtl'
      }
    })
  }

  formloading.value = false
}

const permissionCategories = computed(() => {
  return [...new Set(availablePermissions.value.map(p => p.category))].sort()
})

const trans = (text) => {
  const arr = {
    "*": "دسترسی کامل",
    "users": "کاربران",
    "users.show": "نمایش اطلاعات کاربر",
  }
  return arr[text] || text
}

const getPermissionsByCategory = (category) => {
  return availablePermissions.value.filter(p => p.category === category)
}

const isCurrentCategoryChecked = (category) => {
  if (!currentRole.value) return false
  const categoryPermissions = getPermissionsByCategory(category)
  return categoryPermissions.every(perm => currentRole.value.permissions.includes(perm.key))
}

const toggleCurrentCategoryPermissions = (category) => {
  if (!currentRole.value) return

  const categoryPermissions = getPermissionsByCategory(category)
  const permissionKeys = categoryPermissions.map(perm => perm.key)

  const allSelected = permissionKeys.every(key => currentRole.value.permissions.includes(key))

  if (allSelected) {
    currentRole.value.permissions = currentRole.value.permissions.filter(perm => !permissionKeys.includes(perm))
  } else {
    const newPermissions = [...currentRole.value.permissions]
    permissionKeys.forEach(key => {
      if (!newPermissions.includes(key)) {
        newPermissions.push(key)
      }
    })
    currentRole.value.permissions = newPermissions
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  let dateinit = new Date(dateString)
  let date = dateinit.toLocaleDateString('fa-IR')
  let time = dateinit.toLocaleTimeString('fa-IR')
  return `${date} <span class="text-muted">${time}</span>`
}

const searchFilters = () => {
  if (Object.keys(searchQuery.value).length) getData(itemsPerPage.value, currentPage.value, 0)
}
const resetFilters = () => {
  searchQuery.value = []
  selectedTr.value = []
  currentRowIndex.value = -1
  getData(itemsPerPage.value, currentPage.value, 0)
}


const saveRole = async () => {
  if (!currentRole.value.title.trim()) {
    formError.value = 'عنوان نقش الزامی است'
    return
  }

  formloading.value = true
  formError.value = null

  try {
    const roleData = {
      title: currentRole.value.title,
      permissions: currentRole.value.permissions,
    }

    let response
    if (modalMode.value === 'create') {
      response = await $api('/users/jobs', {
        method: 'POST',
        body: roleData
      })

      if (response?.data) {
        roles.value.unshift(response.data)
      }
    } else if (modalMode.value === 'edit') {
      response = await $api(`/users/jobs/${currentRole.value.id}`, {
        method: 'PUT',
        body: roleData
      })

      if (response?.data) {
        const index = roles.value.findIndex(r => r.id === currentRole.value.id)
        if (index !== -1) {
          roles.value[index] = response.data
        }
      }
    }

    showRoleModal.value = false
    currentRole.value = null

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


const openRoleModal = async (mode = 'create', role = null) => {
  modalMode.value = mode
  formError.value = null

  if (mode === 'create') {
    currentRole.value = {
      title: '',
      permissions: []
    }
  } else if (role) {
    currentRole.value = { ...role }
    const permissions = role.option?.permissions || []
    currentRole.value.permissions = Array.isArray(permissions) ? permissions : []
  }

  showRoleModal.value = true

  await nextTick()
  if (roleTitleInput.value) {
    roleTitleInput.value.focus()
  }
}

const editRole = (role) => {
  openRoleModal('edit', role)
}

const viewRole = (role) => {
  openRoleModal('view', role)
}



const openDeleteModal = async (role, method = '') => {
  let title, text, icon, confirmButtonText, confirmButtonColor

  if (method === 'restore') {
    title = 'بازیابی نقش'
    text = `آیا می‌خواهید نقش "${role.title}" را بازیابی کنید؟`
    icon = 'question'
    confirmButtonText = 'بله، بازیابی کن'
    confirmButtonColor = '#28a745'
  } else if (method === 'delete') {
    title = 'حذف کامل نقش'
    text = `آیا مطمئن هستید که می‌خواهید نقش "${role.title}" را برای همیشه حذف کنید؟ این عمل قابل برگشت نیست!`
    icon = 'error'
    confirmButtonText = 'بله، برای همیشه حذف کن'
    confirmButtonColor = '#dc3545'
  } else {
    title = 'حذف نقش'
    text = `آیا می‌خواهید نقش "${role.title}" را حذف کنید؟`
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
    await confirmDelete(role, method)
  }
}

const confirmDelete = async (role, method = '') => {
  formloading.value = true
  error.value = null

  try {
    let url, httpMethod, successMessage

    if (method === 'restore') {
      url = `/users/jobs/${role.id}/restore`
      httpMethod = 'PATCH'
      successMessage = 'نقش با موفقیت بازیابی شد!'
    } else if (method === 'delete') {
      url = `/users/jobs/${role.id}/force`
      httpMethod = 'DELETE'
      successMessage = 'نقش برای همیشه حذف شد!'
    } else {
      url = `/users/jobs/${role.id}`
      httpMethod = 'DELETE'
      successMessage = 'نقش با موفقیت حذف شد!'
    }

    await $api(url, {
      method: httpMethod
    })

    // به‌روزرسانی لیست بر اساس نوع عملیات
    if (method === 'restore' || method === 'delete') {
      roles.value = roles.value.filter(r => r.id !== role.id)
    } else {
      // برای حذف عادی، نقش را از لیست حذف می‌کنیم
      roles.value = roles.value.filter(r => r.id !== role.id)
    }

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
    const status = err?.response?.status
    const data = err?.response?._data
    let errorMessage = 'خطا در عملیات'

    if (status === 404) {
      errorMessage = 'نقش یافت نشد'
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

<style scoped>
.role-badge {
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

.role-badge-low {
  background: linear-gradient(135deg, #6c757d, #5a6268);
}

.role-badge-medium {
  background: linear-gradient(135deg, #ffc107, #e0a800);
}

.role-badge-high {
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

.progress {
  background-color: #e9ecef;
}

.progress-bar {
  background-color: #0d6efd;
}

.permition-list {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
}

.permition-list div {
  width: 25%;
}

/* SweetAlert2 RTL Support */
.swal-rtl {
  direction: rtl;
}

.swal-rtl .swal2-popup .swal2-actions {
  flex-direction: row-reverse;
}
</style>