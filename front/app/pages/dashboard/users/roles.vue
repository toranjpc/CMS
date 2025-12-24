<template>
  <div class="user-roles-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">نقش‌های کاربری</h1>
          <p class="text-muted mt-1">مدیریت نقش‌ها و سطح دسترسی‌های کاربران سیستم</p>
        </div>
        <button class="btn btn-primary" @click="showAddRoleModal = true">
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
                <option value="1">فعال</option>
                <option value="0">غیرفعال</option>
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
                    <button class="btn text-danger" @click="deleteRole(role)" title="حذف">
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

    <!-- Add Role Modal -->
    <div class="modal fade" :class="{ show: showAddRoleModal }"
      :style="{ display: showAddRoleModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">افزودن نقش جدید</h5>
            <button type="button" class="btn-close" @click="showAddRoleModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addRole">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">نام نقش *</label>
                  <input type="text" class="form-control" v-model="newRole.title" required />
                </div>
                <div class="col-12">
                  <label class="form-label">دسترسی‌ها</label>
                  <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                    <div v-for="category in permissionCategories" :key="category" class="mb-3">
                      <h6 class="text-primary mb-2">{{ category }}</h6>
                      <div class="ms-3">
                        <div class="form-check mb-1" v-for="permission in getPermissionsByCategory(category)"
                          :key="permission.key">
                          <input class="form-check-input" type="checkbox" :id="permission.key"
                            v-model="newRole.permissions" :value="permission.key" />
                          <label class="form-check-label small" :for="permission.key">
                            {{ permission.label }}
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
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showAddRoleModal = false">انصراف</button>
            <button type="button" class="btn btn-primary" @click="addRole">افزودن نقش</button>
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
// Reactive data
const { $api } = useNuxtApp()

const roles = ref([])
const loading = ref(false)
const error = ref(null)
const totalitems = ref(0)
const totalPages = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(10)
const searchQuery = ref([])

const showAddRoleModal = ref(false)
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
    console.error('Error fetching roles and permissions:', err)
    error.value = 'خطا در بارگذاری لیست نقش‌ها'
  } finally {
    loading.value = false
  }
}


const newRole = reactive({
  title: '',
  permissions: []
})


// Load data on component mount
onMounted(() => {
  getData(itemsPerPage.value, currentPage.value, 1)
})


const permissionCategories = computed(() => {
  return [...new Set(availablePermissions.value.map(p => p.category))].sort()
})

const getPermissionsByCategory = (category) => {
  return availablePermissions.value.filter(p => p.category === category)
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




const addRole = () => {
  if (!newRole.title.trim()) return

  const role = {
    id: Date.now(), // Temporary ID
    f_id: null,
    title: newRole.title,
    kind: 'job',
    option: {
      form: null,
      permissions: newRole.permissions.length > 0 ? newRole.permissions : []
    },
    status: 1,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
    deleted_at: null
  }

  roles.value.push(role)

  // Reset form
  Object.assign(newRole, {
    title: '',
    permissions: []
  })

  showAddRoleModal.value = false
  alert('نقش با موفقیت اضافه شد!')
}

const editRole = (role) => {
  alert(`ویرایش نقش: ${role.title}`)
}

const viewRole = (role) => {
  alert(`مشاهده نقش: ${role.title}`)
}


const deleteRole = (role) => {
  if (confirm(`آیا از حذف نقش "${role.title}" اطمینان دارید؟`)) {
    roles.value = roles.value.filter(r => r.id !== role.id)
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
</style>