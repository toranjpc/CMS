<template>
  <div class="users-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">مدیریت کاربران</h1>
          <p class="text-muted mt-1">مدیریت و مشاهده لیست کاربران سیستم</p>
        </div>
        <button class="btn btn-primary" @click="showAddUserModal = true">
          <i class="bi bi-plus-circle me-1"></i>
          افزودن کاربر
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
              placeholder="جستجو بر اساس نام یا ایمیل..."
              v-model="searchQuery"
            />
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="active">فعال</option>
              <option value="inactive">غیرفعال</option>
              <option value="pending">در انتظار</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="roleFilter">
              <option value="">همه نقش‌ها</option>
              <option value="admin">مدیر</option>
              <option value="editor">ویرایشگر</option>
              <option value="user">کاربر</option>
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
          <i class="bi bi-exclamation-triangle me-2"></i>
          {{ error }}
          <button class="btn btn-sm btn-outline-danger ms-2" @click="fetchUsers">
            <i class="bi bi-arrow-clockwise"></i>
            تلاش مجدد
          </button>
        </div>

        <!-- Users Table -->
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>نام و نام خانوادگی</th>
                <th>ایمیل</th>
                <th>نقش</th>
                <th>وضعیت</th>
                <th>تاریخ عضویت</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar me-3">
                      <img :src="user.avatar" :alt="user.name" class="rounded-circle" width="40" height="40" />
                    </div>
                    <div>
                      <div class="fw-semibold">{{ user.name }}</div>
                      <small class="text-muted">{{ user.username }}</small>
                    </div>
                  </div>
                </td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="badge" :class="getRoleBadgeClass(user.role)">
                    {{ getRoleLabel(user.role) }}
                  </span>
                </td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(user.status)">
                    {{ getStatusLabel(user.status) }}
                  </span>
                </td>
                <td>{{ formatDate(user.createdAt) }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" @click="editUser(user)" title="ویرایش">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-info" @click="viewUser(user)" title="مشاهده">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-danger" @click="deleteUser(user)" title="حذف">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <!-- Empty State -->
              <tr v-if="filteredUsers.length === 0">
                <td colspan="6" class="text-center py-5">
                  <div class="text-muted">
                    <i class="bi bi-people display-4 mb-3"></i>
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
            نمایش {{ (currentPage - 1) * itemsPerPage + 1 }} تا {{ Math.min(currentPage * itemsPerPage, filteredUsers.length) }} از {{ filteredUsers.length }} کاربر
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

    <!-- Add User Modal (simplified) -->
    <div class="modal fade" :class="{ show: showAddUserModal }" :style="{ display: showAddUserModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">افزودن کاربر جدید</h5>
            <button type="button" class="btn-close" @click="showAddUserModal = false"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted">این قابلیت در نسخه کامل پیاده‌سازی خواهد شد.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showAddUserModal = false">بستن</button>
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
const roleFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const showAddUserModal = ref(false)
const users = ref([])
const loading = ref(false)
const error = ref(null)

// Get API instance
const { $api } = useNuxtApp()

// Fetch users from API
const fetchUsers = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api('/users') // Adjust endpoint as needed
    users.value = response.data || response
  } catch (err) {
    console.error('Error fetching users:', err)
    error.value = 'خطا در بارگذاری لیست کاربران'

    // Fallback to mock data if API fails
    users.value = [
      {
        id: 1,
        name: 'احمد رضایی',
        username: 'ahmad_r',
        email: 'ahmad@example.com',
        role: 'admin',
        status: 'active',
        avatar: 'https://via.placeholder.com/40',
        createdAt: '2024-01-15T10:30:00Z'
      },
      {
        id: 2,
        name: 'مریم احمدی',
        username: 'maryam_a',
        email: 'maryam@example.com',
        role: 'editor',
        status: 'active',
        avatar: 'https://via.placeholder.com/40',
        createdAt: '2024-02-20T14:15:00Z'
      },
      {
        id: 3,
        name: 'علی محمدی',
        username: 'ali_m',
        email: 'ali@example.com',
        role: 'user',
        status: 'inactive',
        avatar: 'https://via.placeholder.com/40',
        createdAt: '2024-03-10T09:45:00Z'
      },
      {
        id: 4,
        name: 'فاطمه کریمی',
        username: 'fatemeh_k',
        email: 'fatemeh@example.com',
        role: 'editor',
        status: 'active',
        avatar: 'https://via.placeholder.com/40',
        createdAt: '2024-01-25T16:20:00Z'
      },
      {
        id: 5,
        name: 'حسن ابراهیمی',
        username: 'hasan_i',
        email: 'hasan@example.com',
        role: 'user',
        status: 'pending',
        avatar: 'https://via.placeholder.com/40',
        createdAt: '2024-04-05T11:30:00Z'
      }
    ]
  } finally {
    loading.value = false
  }
}

// Load users on component mount
onMounted(() => {
  fetchUsers()
})

// Computed properties
const filteredUsers = computed(() => {
  let filtered = users.value

  if (searchQuery.value) {
    filtered = filtered.filter(user =>
      user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(user => user.status === statusFilter.value)
  }

  if (roleFilter.value) {
    filtered = filtered.filter(user => user.role === roleFilter.value)
  }

  return filtered
})

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / itemsPerPage.value)
})

// Methods
const getRoleLabel = (role) => {
  const labels = {
    admin: 'مدیر',
    editor: 'ویرایشگر',
    user: 'کاربر'
  }
  return labels[role] || role
}

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'bg-danger',
    editor: 'bg-warning',
    user: 'bg-secondary'
  }
  return classes[role] || 'bg-secondary'
}

const getStatusLabel = (status) => {
  const labels = {
    active: 'فعال',
    inactive: 'غیرفعال',
    pending: 'در انتظار'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-success',
    inactive: 'bg-secondary',
    pending: 'bg-warning'
  }
  return classes[status] || 'bg-secondary'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fa-IR')
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  roleFilter.value = ''
  currentPage.value = 1
}

const editUser = (user) => {
  alert(`ویرایش کاربر: ${user.name}`)
}

const viewUser = (user) => {
  alert(`مشاهده کاربر: ${user.name}`)
}

const deleteUser = (user) => {
  if (confirm(`آیا از حذف کاربر ${user.name} اطمینان دارید؟`)) {
    users.value = users.value.filter(u => u.id !== user.id)
  }
}

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
  title: 'مدیریت کاربران - CMS Panel'
})
</script>

<style scoped>
.avatar img {
  object-fit: cover;
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
</style>
