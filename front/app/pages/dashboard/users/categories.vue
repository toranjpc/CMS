<template>
  <div class="user-categories-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">دسته‌بندی کاربران</h1>
          <p class="text-muted mt-1">مدیریت دسته‌بندی‌ها و گروه‌بندی کاربران سیستم</p>
        </div>
        <button class="btn btn-primary" @click="showAddCategoryModal = true">
          <i class="bi bi-plus-circle me-1"></i>
          افزودن دسته‌بندی
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
              placeholder="جستجو بر اساس نام دسته..."
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
            <select class="form-select" v-model="sortBy">
              <option value="name">مرتب‌سازی بر اساس نام</option>
              <option value="createdAt">مرتب‌سازی بر اساس تاریخ</option>
              <option value="userCount">مرتب‌سازی بر اساس تعداد کاربر</option>
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

    <!-- Categories Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>نام دسته</th>
                <th>توضیحات</th>
                <th>تعداد کاربران</th>
                <th>وضعیت</th>
                <th>تاریخ ایجاد</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="category in filteredCategories" :key="category.id">
                <td>
                  <div class="d-flex align-items-center">
                    <div class="category-icon me-3" :style="{ backgroundColor: category.color }">
                      <i :class="category.icon" class="text-white"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">{{ category.name }}</div>
                      <small class="text-muted">{{ category.slug }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="text-truncate d-inline-block" style="max-width: 200px;" :title="category.description">
                    {{ category.description }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-info">{{ category.userCount }}</span>
                </td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(category.status)">
                    {{ getStatusLabel(category.status) }}
                  </span>
                </td>
                <td>{{ formatDate(category.createdAt) }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" @click="editCategory(category)" title="ویرایش">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-info" @click="viewCategory(category)" title="مشاهده">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-danger" @click="deleteCategory(category)" title="حذف">
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
            نمایش {{ (currentPage - 1) * itemsPerPage + 1 }} تا {{ Math.min(currentPage * itemsPerPage, filteredCategories.length) }} از {{ filteredCategories.length }} دسته‌بندی
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

    <!-- Add Category Modal -->
    <div class="modal fade" :class="{ show: showAddCategoryModal }" :style="{ display: showAddCategoryModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">افزودن دسته‌بندی جدید</h5>
            <button type="button" class="btn-close" @click="showAddCategoryModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addCategory">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">نام دسته‌بندی *</label>
                  <input type="text" class="form-control" v-model="newCategory.name" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">نامک (Slug)</label>
                  <input type="text" class="form-control" v-model="newCategory.slug" placeholder="auto-generated" readonly />
                </div>
                <div class="col-12">
                  <label class="form-label">توضیحات</label>
                  <textarea class="form-control" rows="3" v-model="newCategory.description"></textarea>
                </div>
                <div class="col-md-4">
                  <label class="form-label">رنگ</label>
                  <input type="color" class="form-control" v-model="newCategory.color" />
                </div>
                <div class="col-md-4">
                  <label class="form-label">آیکون</label>
                  <select class="form-select" v-model="newCategory.icon">
                    <option value="bi bi-circle-fill">دایره</option>
                    <option value="bi bi-star-fill">ستاره</option>
                    <option value="bi bi-heart-fill">قلب</option>
                    <option value="bi bi-shield-fill">سپر</option>
                    <option value="bi bi-gear-fill">چرخ‌دنده</option>
                    <option value="bi bi-house-fill">خانه</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">وضعیت</label>
                  <select class="form-select" v-model="newCategory.status">
                    <option value="active">فعال</option>
                    <option value="inactive">غیرفعال</option>
                  </select>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showAddCategoryModal = false">انصراف</button>
            <button type="button" class="btn btn-primary" @click="addCategory">افزودن دسته‌بندی</button>
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
const sortBy = ref('name')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const showAddCategoryModal = ref(false)

const newCategory = reactive({
  name: '',
  slug: '',
  description: '',
  color: '#007bff',
  icon: 'bi bi-circle-fill',
  status: 'active'
})

// Mock categories data
const categories = ref([
  {
    id: 1,
    name: 'مدیران سیستم',
    slug: 'system-admins',
    description: 'کاربرانی با دسترسی کامل به سیستم مدیریت',
    color: '#dc3545',
    icon: 'bi bi-shield-fill',
    status: 'active',
    userCount: 5,
    createdAt: '2024-01-10T08:00:00Z'
  },
  {
    id: 2,
    name: 'ویرایشگران محتوا',
    slug: 'content-editors',
    description: 'کاربران مسئول ایجاد و ویرایش محتوای سایت',
    color: '#ffc107',
    icon: 'bi bi-pencil-fill',
    status: 'active',
    userCount: 12,
    createdAt: '2024-01-15T10:30:00Z'
  },
  {
    id: 3,
    name: 'کاربران عادی',
    slug: 'regular-users',
    description: 'کاربران ثبت‌نام شده عادی سیستم',
    color: '#6c757d',
    icon: 'bi bi-person-fill',
    status: 'active',
    userCount: 245,
    createdAt: '2024-01-01T00:00:00Z'
  },
  {
    id: 4,
    name: 'نویسندگان مهمان',
    slug: 'guest-writers',
    description: 'نویسندگان مهمان با دسترسی محدود',
    color: '#17a2b8',
    icon: 'bi bi-pen-fill',
    status: 'active',
    userCount: 8,
    createdAt: '2024-02-01T14:20:00Z'
  },
  {
    id: 5,
    name: 'کاربران ویژه',
    slug: 'premium-users',
    description: 'کاربران دارای اشتراک ویژه',
    color: '#28a745',
    icon: 'bi bi-star-fill',
    status: 'inactive',
    userCount: 0,
    createdAt: '2024-03-01T09:15:00Z'
  }
])

// Computed properties
const filteredCategories = computed(() => {
  let filtered = categories.value

  if (searchQuery.value) {
    filtered = filtered.filter(category =>
      category.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      category.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(category => category.status === statusFilter.value)
  }

  // Sort
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return a.name.localeCompare(b.name, 'fa')
      case 'createdAt':
        return new Date(b.createdAt) - new Date(a.createdAt)
      case 'userCount':
        return b.userCount - a.userCount
      default:
        return 0
    }
  })

  return filtered
})

const totalPages = computed(() => {
  return Math.ceil(filteredCategories.value.length / itemsPerPage.value)
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

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fa-IR')
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  sortBy.value = 'name'
  currentPage.value = 1
}

const addCategory = () => {
  if (!newCategory.name.trim()) return

  // Generate slug if empty
  if (!newCategory.slug) {
    newCategory.slug = newCategory.name.toLowerCase().replace(/\s+/g, '-').replace(/[^\w\-]/g, '')
  }

  const category = {
    id: categories.value.length + 1,
    ...newCategory,
    userCount: 0,
    createdAt: new Date().toISOString()
  }

  categories.value.push(category)

  // Reset form
  Object.assign(newCategory, {
    name: '',
    slug: '',
    description: '',
    color: '#007bff',
    icon: 'bi bi-circle-fill',
    status: 'active'
  })

  showAddCategoryModal.value = false
  alert('دسته‌بندی با موفقیت اضافه شد!')
}

const editCategory = (category) => {
  alert(`ویرایش دسته‌بندی: ${category.name}`)
}

const viewCategory = (category) => {
  alert(`مشاهده دسته‌بندی: ${category.name}`)
}

const deleteCategory = (category) => {
  if (confirm(`آیا از حذف دسته‌بندی "${category.name}" اطمینان دارید؟`)) {
    categories.value = categories.value.filter(c => c.id !== category.id)
  }
}

// Watch for name changes to auto-generate slug
watch(() => newCategory.name, (newName) => {
  if (newName) {
    newCategory.slug = newName.toLowerCase().replace(/\s+/g, '-').replace(/[^\w\-]/g, '')
  }
})

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
  title: 'دسته‌بندی کاربران - CMS Panel'
})
</script>

<style scoped>
.category-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
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