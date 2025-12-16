<template>
  <div class="user-roles-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">نقش‌های کاربری</h1>
          <p class="text-muted mt-1">مدیریت نقش‌ها و سطح دسترسی‌های کاربران سیستم</p>
        </div>
        <button class="btn btn-primary" @click="showAddRoleModal = true">
          <i class="bi bi-plus-circle me-1"></i>
          افزودن نقش
        </button>
      </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <input
              type="text"
              class="form-control"
              placeholder="جستجو بر اساس نام نقش..."
              v-model="searchQuery"
            />
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="active">فعال</option>
              <option value="inactive">غیرفعال</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="levelFilter">
              <option value="">همه سطوح</option>
              <option value="1">سطح ۱ (پایین)</option>
              <option value="2">سطح ۲</option>
              <option value="3">سطح ۳</option>
              <option value="4">سطح ۴</option>
              <option value="5">سطح ۵ (بالا)</option>
            </select>
          </div>
          <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100" @click="resetFilters">
              پاک کردن فیلترها
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Roles Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>نام نقش</th>
                <th>توضیحات</th>
                <th>سطح دسترسی</th>
                <th>مجوزها</th>
                <th>تعداد کاربران</th>
                <th>وضعیت</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="role in filteredRoles" :key="role.id">
                <td>
                  <div class="d-flex align-items-center">
                    <div class="role-badge me-3" :class="getRoleBadgeClass(role.level)">
                      {{ role.level }}
                    </div>
                    <div>
                      <div class="fw-semibold">{{ role.name }}</div>
                      <small class="text-muted">{{ role.code }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="text-truncate d-inline-block" style="max-width: 200px;" :title="role.description">
                    {{ role.description }}
                  </span>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                      <div class="progress-bar" :style="{ width: (role.level / 5) * 100 + '%' }"></div>
                    </div>
                    <small class="text-muted">{{ role.level }}/5</small>
                  </div>
                </td>
                <td>
                  <div class="permissions-list">
                    <span v-for="permission in role.permissions.slice(0, 2)" :key="permission" class="badge bg-light text-dark me-1 mb-1">
                      {{ permission }}
                    </span>
                    <span v-if="role.permissions.length > 2" class="badge bg-secondary">
                      +{{ role.permissions.length - 2 }}
                    </span>
                  </div>
                </td>
                <td>
                  <span class="badge bg-info">{{ role.userCount }}</span>
                </td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(role.status)">
                    {{ getStatusLabel(role.status) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" @click="editRole(role)" title="ویرایش">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-info" @click="viewRole(role)" title="مشاهده">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-warning" @click="managePermissions(role)" title="مدیریت مجوزها">
                      <i class="bi bi-key"></i>
                    </button>
                    <button class="btn btn-outline-danger" @click="deleteRole(role)" title="حذف" :disabled="role.isSystem">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted">
            نمایش {{ (currentPage - 1) * itemsPerPage + 1 }} تا {{ Math.min(currentPage * itemsPerPage, filteredRoles.length) }} از {{ filteredRoles.length }} نقش
          </div>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <a class="page-link" href="#" @click.prevent="currentPage--" v-if="currentPage > 1">قبلی</a>
              </li>
              <li class="page-item" :class="{ active: page === currentPage }" v-for="page in totalPages" :key="page">
                <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <a class="page-link" href="#" @click.prevent="currentPage++" v-if="currentPage < totalPages">بعدی</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" :class="{ show: showAddRoleModal }" :style="{ display: showAddRoleModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">افزودن نقش جدید</h5>
            <button type="button" class="btn-close" @click="showAddRoleModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addRole">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">نام نقش *</label>
                  <input type="text" class="form-control" v-model="newRole.name" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">کد نقش</label>
                  <input type="text" class="form-control" v-model="newRole.code" placeholder="مثال: admin, editor" />
                </div>
                <div class="col-12">
                  <label class="form-label">توضیحات</label>
                  <textarea class="form-control" rows="3" v-model="newRole.description"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">سطح دسترسی (1-5)</label>
                  <input type="range" class="form-range" min="1" max="5" v-model="newRole.level" />
                  <div class="d-flex justify-content-between">
                    <small class="text-muted">پایین</small>
                    <small class="fw-semibold">سطح {{ newRole.level }}</small>
                    <small class="text-muted">بالا</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">وضعیت</label>
                  <select class="form-select" v-model="newRole.status">
                    <option value="active">فعال</option>
                    <option value="inactive">غیرفعال</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label">مجوزهای پایه</label>
                  <div class="row g-2">
                    <div class="col-md-6" v-for="permission in availablePermissions" :key="permission.key">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" :id="permission.key" v-model="newRole.permissions" :value="permission.key" />
                        <label class="form-check-label" :for="permission.key">
                          {{ permission.label }}
                        </label>
                      </div>
                    </div>
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


// Reactive data
const searchQuery = ref('')
const statusFilter = ref('')
const levelFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const showAddRoleModal = ref(false)

const newRole = reactive({
  name: '',
  code: '',
  description: '',
  level: 1,
  status: 'active',
  permissions: []
})

// Available permissions
const availablePermissions = [
  { key: 'users.view', label: 'مشاهده کاربران' },
  { key: 'users.create', label: 'ایجاد کاربر' },
  { key: 'users.edit', label: 'ویرایش کاربران' },
  { key: 'users.delete', label: 'حذف کاربران' },
  { key: 'content.view', label: 'مشاهده محتوا' },
  { key: 'content.create', label: 'ایجاد محتوا' },
  { key: 'content.edit', label: 'ویرایش محتوا' },
  { key: 'content.publish', label: 'انتشار محتوا' },
  { key: 'content.delete', label: 'حذف محتوا' },
  { key: 'settings.view', label: 'مشاهده تنظیمات' },
  { key: 'settings.edit', label: 'ویرایش تنظیمات' },
  { key: 'reports.view', label: 'مشاهده گزارشات' },
  { key: 'system.admin', label: 'دسترسی ادمین سیستم' }
]

// Mock roles data
const roles = ref([
  {
    id: 1,
    name: 'مدیر کل سیستم',
    code: 'super_admin',
    description: 'دسترسی کامل به تمام بخش‌های سیستم',
    level: 5,
    status: 'active',
    isSystem: true,
    userCount: 2,
    permissions: ['users.view', 'users.create', 'users.edit', 'users.delete', 'content.view', 'content.create', 'content.edit', 'content.publish', 'content.delete', 'settings.view', 'settings.edit', 'reports.view', 'system.admin']
  },
  {
    id: 2,
    name: 'مدیر محتوا',
    code: 'content_admin',
    description: 'مدیریت کامل محتوای سایت',
    level: 4,
    status: 'active',
    isSystem: false,
    userCount: 3,
    permissions: ['content.view', 'content.create', 'content.edit', 'content.publish', 'content.delete', 'users.view']
  },
  {
    id: 3,
    name: 'ویرایشگر',
    code: 'editor',
    description: 'ایجاد و ویرایش محتوای سایت',
    level: 3,
    status: 'active',
    isSystem: false,
    userCount: 12,
    permissions: ['content.view', 'content.create', 'content.edit', 'users.view']
  },
  {
    id: 4,
    name: 'نویسنده',
    code: 'writer',
    description: 'ایجاد محتوای جدید',
    level: 2,
    status: 'active',
    isSystem: false,
    userCount: 8,
    permissions: ['content.view', 'content.create']
  },
  {
    id: 5,
    name: 'کاربر عادی',
    code: 'user',
    description: 'دسترسی پایه به سیستم',
    level: 1,
    status: 'active',
    isSystem: true,
    userCount: 245,
    permissions: ['content.view']
  },
  {
    id: 6,
    name: 'ناظر',
    code: 'viewer',
    description: 'دسترسی فقط خواندنی',
    level: 1,
    status: 'inactive',
    isSystem: false,
    userCount: 0,
    permissions: ['content.view', 'users.view', 'reports.view']
  }
])

// Computed properties
const filteredRoles = computed(() => {
  let filtered = roles.value

  if (searchQuery.value) {
    filtered = filtered.filter(role =>
      role.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      role.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      role.code.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(role => role.status === statusFilter.value)
  }

  if (levelFilter.value) {
    filtered = filtered.filter(role => role.level === parseInt(levelFilter.value))
  }

  return filtered
})

const totalPages = computed(() => {
  return Math.ceil(filteredRoles.value.length / itemsPerPage.value)
})

// Methods
const getStatusLabel = (status) => {
  const labels = {
    active: 'فعال',
    inactive: 'غیرفعال'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-success',
    inactive: 'bg-secondary'
  }
  return classes[status] || 'bg-secondary'
}

const getRoleBadgeClass = (level) => {
  const classes = {
    1: 'role-badge-low',
    2: 'role-badge-medium',
    3: 'role-badge-medium',
    4: 'role-badge-high',
    5: 'role-badge-high'
  }
  return classes[level] || 'role-badge-low'
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  levelFilter.value = ''
  currentPage.value = 1
}

const addRole = () => {
  if (!newRole.name.trim()) return

  // Generate code if empty
  if (!newRole.code) {
    newRole.code = newRole.name.toLowerCase().replace(/\s+/g, '_').replace(/[^\w_]/g, '')
  }

  const role = {
    id: roles.value.length + 1,
    ...newRole,
    isSystem: false,
    userCount: 0
  }

  roles.value.push(role)

  // Reset form
  Object.assign(newRole, {
    name: '',
    code: '',
    description: '',
    level: 1,
    status: 'active',
    permissions: []
  })

  showAddRoleModal.value = false
  alert('نقش با موفقیت اضافه شد!')
}

const editRole = (role) => {
  alert(`ویرایش نقش: ${role.name}`)
}

const viewRole = (role) => {
  alert(`مشاهده نقش: ${role.name}`)
}

const managePermissions = (role) => {
  alert(`مدیریت مجوزهای نقش: ${role.name}`)
}

const deleteRole = (role) => {
  if (role.isSystem) {
    alert('نمی‌توان نقش‌های سیستمی را حذف کرد!')
    return
  }

  if (confirm(`آیا از حذف نقش "${role.name}" اطمینان دارید؟`)) {
    roles.value = roles.value.filter(r => r.id !== role.id)
  }
}

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
  title: 'نقش‌های کاربری - CMS Panel'
})
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

.permissions-list {
  max-width: 200px;
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