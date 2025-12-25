<template>
  <div class="user-roles-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">نقش‌های کاربری</h1>
          <p class="text-muted mt-1">مدیریت نقش‌ها و سطح دسترسی‌های کاربران سیستم</p>
        </div>
        <button class="btn btn-primary" @click="openRoleModal('create')">
          <i class="fa fa-plus-circle me-1"></i>
          افزودن نقش
        </button>
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
                <th width="10%">شناسه</th>
                <th width="">عنوان</th>
                <th width="20%">تاریخ ایجاد</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="role in roles" :key="role.id">
                <td>{{ role.id }}</td>
                <td>{{ role.title }}

                  <div class="actionBTN btn-group btn-group-sm float-end">
                    <button class="btn text-primary" @click="editRole(role)" title="ویرایش">
                      <i class="fa fa-pencil"></i>
                    </button>
                    <button class="btn text-info" @click="viewRole(role)" title="مشاهده">
                      <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn text-warning" v-if="searchQuery.status == 'deleted'"
                      @click="openDeleteModal(role, 'restore')" title="بازیافت">
                      <i class="fa fa-refresh"></i>
                    </button>
                    <button class="btn text-danger" v-if="searchQuery.status == 'deleted'"
                      @click="openDeleteModal(role, 'delete')" title="حذف برای همیشه">
                      <i class="fa fa-times"></i>
                    </button>
                    <button class="btn text-danger" v-else @click="openDeleteModal(role)" title="حذف">
                      <i class="fa fa-times"></i>
                    </button>
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
                    required />
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" :class="{ show: showDeleteModal }" :style="{ display: showDeleteModal ? 'block' : 'none' }"
      tabindex="-1">
      <div class="shadow" @click="cancelDelete"></div>
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">
              <i class="fa fa-exclamation-triangle me-2"></i>
              تأیید حذف نقش
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="cancelDelete"></button>
          </div>
          <div class="modal-body text-center py-4">
            <div class="mb-3">
              <i class="fa fa-trash-alt fa-3x text-danger mb-3"></i>
            </div>
            <h5 class="mb-3">آیا از حذف این نقش اطمینان دارید؟</h5>
            <p class="text-muted mb-0" v-if="roleToDelete">
              نقش "<strong>{{ roleToDelete.title }}</strong>" به طور کامل حذف خواهد شد و قابل بازیابی نخواهد بود.
            </p>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-secondary px-4" @click="cancelDelete" :disabled="formloading">
              <i class="fa fa-times me-1"></i>
              لغو
            </button>
            <button type="button" class="btn btn-danger px-4" @click="confirmDelete" :disabled="formloading">
              <span v-if="formloading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              <i class="fa fa-trash me-1" v-else></i>
              حذف نقش
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
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

const showRoleModal = ref(false)
const modalMode = ref('create') /* 'create', 'edit', 'view' */
const currentRole = ref(null)
const showDeleteModal = ref(false)
const DeleteModalMethode = ref('')
const roleToDelete = ref(null)
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
    // console.log(response)

    roles.value = response.data?.items || []
    totalitems.value = response.data?.total || 0
    totalPages.value = response.data?.last_page || 0
    currentPage.value = response.data?.current_page || 1

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
})


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
      console.log(response)

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


const openRoleModal = (mode = 'create', role = null) => {
  modalMode.value = mode

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
}

const editRole = (role) => {
  formError.value = null
  openRoleModal('edit', role)
}

const viewRole = (role) => {
  formError.value = null
  openRoleModal('view', role)
}



const openDeleteModal = (role, method = '') => {
  roleToDelete.value = role
  showDeleteModal.value = true
  DeleteModalMethode.value = method
}

const confirmDelete = async () => {
  if (!roleToDelete.value) return

  formloading.value = true
  error.value = null

  try {
    await $api(`/users/jobs/${roleToDelete.value.id}`, {
      method: 'DELETE',
      body: { action: DeleteModalMethode.value }
    })

    // حذف نقش از لیست
    roles.value = roles.value.filter(r => r.id !== roleToDelete.value.id)

    // بستن مدال و ریست کردن
    showDeleteModal.value = false
    roleToDelete.value = null

    // نمایش پیام موفقیت
    alert('نقش با موفقیت حذف شد!')

  } catch (err) {
    console.error('Error deleting role:', err)
    const status = err?.response?.status
    const data = err?.response?._data

    if (status === 404) {
      error.value = 'نقش یافت نشد'
    } else if (status === 403) {
      error.value = 'شما دسترسی حذف این نقش را ندارید'
    } else if (data?.message) {
      error.value = data.message
    } else {
      error.value = 'خطا در حذف نقش'
    }
  } finally {
    formloading.value = false
  }
}

const cancelDelete = () => {
  showDeleteModal.value = false
  roleToDelete.value = null
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
</style>