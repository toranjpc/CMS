<template>
  <div class="user-plans-page">
    <div class="row mb-4">
      <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
          <h1 class="fw-bold mb-0">پل‌های کاربری</h1>
          <p class="text-muted mt-1">مدیریت پل‌ها و اشتراک‌های کاربران سیستم</p>
        </div>
        <button class="btn btn-primary" @click="showAddPlanModal = true">
          <i class="bi bi-plus-circle me-1"></i>
          افزودن پل
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
              placeholder="جستجو بر اساس نام پل..."
              v-model="searchQuery"
            />
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="active">فعال</option>
              <option value="inactive">غیرفعال</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="typeFilter">
              <option value="">همه انواع</option>
              <option value="monthly">ماهانه</option>
              <option value="yearly">سالانه</option>
              <option value="lifetime">دائمی</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="popularFilter">
              <option value="">همه پل‌ها</option>
              <option value="true">پل‌های محبوب</option>
              <option value="false">پل‌های عادی</option>
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

    <!-- Plans Grid -->
    <div class="row">
      <div class="col-md-4 mb-4" v-for="plan in filteredPlans" :key="plan.id">
        <div class="card h-100 plan-card" :class="{ 'plan-popular': plan.isPopular }">
          <div class="card-header text-center" v-if="plan.isPopular">
            <span class="badge bg-warning text-dark popular-badge">
              <i class="bi bi-star-fill me-1"></i>
              محبوب‌ترین
            </span>
          </div>
          <div class="card-body d-flex flex-column">
            <div class="text-center mb-3">
              <h5 class="card-title fw-bold">{{ plan.name }}</h5>
              <div class="plan-price mb-2">
                <span class="price-amount">{{ formatPrice(plan.price) }}</span>
                <span class="price-unit">{{ getPriceUnit(plan.type) }}</span>
              </div>
              <span class="badge" :class="getTypeBadgeClass(plan.type)">
                {{ getTypeLabel(plan.type) }}
              </span>
            </div>

            <p class="card-text text-muted text-center mb-3">{{ plan.description }}</p>

            <div class="features-list mb-3">
              <div class="feature-item" v-for="feature in plan.features.slice(0, 4)" :key="feature">
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                {{ feature }}
              </div>
              <div v-if="plan.features.length > 4" class="feature-item text-muted">
                <i class="bi bi-plus-circle me-2"></i>
                و {{ plan.features.length - 4 }} امکان دیگر...
              </div>
            </div>

            <div class="plan-stats mb-3">
              <div class="row text-center">
                <div class="col-6">
                  <div class="stat-number">{{ plan.userCount }}</div>
                  <small class="text-muted">کاربر</small>
                </div>
                <div class="col-6">
                  <div class="stat-number">{{ plan.maxUsers || '∞' }}</div>
                  <small class="text-muted">حداکثر</small>
                </div>
              </div>
            </div>

            <div class="plan-status mb-3">
              <span class="badge w-100" :class="getStatusBadgeClass(plan.status)">
                {{ getStatusLabel(plan.status) }}
              </span>
            </div>

            <div class="mt-auto">
              <div class="btn-group w-100">
                <button class="btn btn-outline-primary btn-sm" @click="editPlan(plan)">
                  <i class="bi bi-pencil me-1"></i>
                  ویرایش
                </button>
                <button class="btn btn-outline-info btn-sm" @click="viewPlan(plan)">
                  <i class="bi bi-eye me-1"></i>
                  مشاهده
                </button>
                <button class="btn btn-outline-danger btn-sm" @click="deletePlan(plan)">
                  <i class="bi bi-trash me-1"></i>
                  حذف
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Plans Table View (Alternative) -->
    <div class="card mt-4" v-if="viewMode === 'table'">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>نام پل</th>
                <th>قیمت</th>
                <th>نوع</th>
                <th>کاربران</th>
                <th>وضعیت</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="plan in filteredPlans" :key="plan.id">
                <td>
                  <div class="d-flex align-items-center">
                    <div v-if="plan.isPopular" class="popular-indicator me-2">
                      <i class="bi bi-star-fill text-warning"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">{{ plan.name }}</div>
                      <small class="text-muted">{{ plan.description }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="fw-semibold">{{ formatPrice(plan.price) }}</div>
                  <small class="text-muted">{{ getPriceUnit(plan.type) }}</small>
                </td>
                <td>
                  <span class="badge" :class="getTypeBadgeClass(plan.type)">
                    {{ getTypeLabel(plan.type) }}
                  </span>
                </td>
                <td>{{ plan.userCount }} / {{ plan.maxUsers || '∞' }}</td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(plan.status)">
                    {{ getStatusLabel(plan.status) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" @click="editPlan(plan)" title="ویرایش">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-info" @click="viewPlan(plan)" title="مشاهده">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-danger" @click="deletePlan(plan)" title="حذف">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- View Toggle -->
    <div class="d-flex justify-content-end mt-3">
      <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-secondary" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'">
          <i class="bi bi-grid"></i>
        </button>
        <button class="btn btn-outline-secondary" :class="{ active: viewMode === 'table' }" @click="viewMode = 'table'">
          <i class="bi bi-list"></i>
        </button>
      </div>
    </div>

    <!-- Add Plan Modal -->
    <div class="modal fade" :class="{ show: showAddPlanModal }" :style="{ display: showAddPlanModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">افزودن پل جدید</h5>
            <button type="button" class="btn-close" @click="showAddPlanModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addPlan">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">نام پل *</label>
                  <input type="text" class="form-control" v-model="newPlan.name" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">نوع پل</label>
                  <select class="form-select" v-model="newPlan.type">
                    <option value="monthly">ماهانه</option>
                    <option value="yearly">سالانه</option>
                    <option value="lifetime">دائمی</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">قیمت (تومان)</label>
                  <input type="number" class="form-control" v-model="newPlan.price" min="0" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">حداکثر کاربران (اختیاری)</label>
                  <input type="number" class="form-control" v-model="newPlan.maxUsers" min="0" placeholder="بی‌نهایت" />
                </div>
                <div class="col-12">
                  <label class="form-label">توضیحات</label>
                  <textarea class="form-control" rows="3" v-model="newPlan.description"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">وضعیت</label>
                  <select class="form-select" v-model="newPlan.status">
                    <option value="active">فعال</option>
                    <option value="inactive">غیرفعال</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">پل محبوب</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" v-model="newPlan.isPopular" />
                    <label class="form-check-label">این پل را به عنوان محبوب نمایش بده</label>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label">امکانات پل</label>
                  <div class="features-input">
                    <div class="input-group mb-2" v-for="(feature, index) in newPlan.features" :key="index">
                      <input type="text" class="form-control" v-model="newPlan.features[index]" placeholder="مثال: دسترسی به API" />
                      <button class="btn btn-outline-danger" type="button" @click="removeFeature(index)">
                        <i class="bi bi-x"></i>
                      </button>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" type="button" @click="addFeature">
                      <i class="bi bi-plus me-1"></i>
                      افزودن امکان
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showAddPlanModal = false">انصراف</button>
            <button type="button" class="btn btn-primary" @click="addPlan">افزودن پل</button>
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
const typeFilter = ref('')
const popularFilter = ref('')
const viewMode = ref('grid')
const showAddPlanModal = ref(false)

const newPlan = reactive({
  name: '',
  description: '',
  price: 0,
  type: 'monthly',
  maxUsers: null,
  status: 'active',
  isPopular: false,
  features: ['']
})

// Mock plans data
const plans = ref([
  {
    id: 1,
    name: 'پل رایگان',
    description: 'پل پایه برای شروع',
    price: 0,
    type: 'lifetime',
    maxUsers: 1,
    status: 'active',
    isPopular: false,
    userCount: 245,
    features: [
      'دسترسی به پنل کاربری',
      '۵ صفحه محتوا',
      'پشتیبانی ایمیلی',
      'آمار پایه'
    ]
  },
  {
    id: 2,
    name: 'پل استاندارد',
    description: 'مناسب برای کسب‌وکارهای کوچک',
    price: 290000,
    type: 'monthly',
    maxUsers: 10,
    status: 'active',
    isPopular: false,
    userCount: 89,
    features: [
      'تمام امکانات رایگان',
      '۵۰ صفحه محتوا',
      'پشتیبانی چت آنلاین',
      'آمار پیشرفته',
      'API دسترسی',
      'بکاپ روزانه'
    ]
  },
  {
    id: 3,
    name: 'پل حرفه‌ای',
    description: 'برای کسب‌وکارهای در حال رشد',
    price: 590000,
    type: 'monthly',
    maxUsers: 50,
    status: 'active',
    isPopular: true,
    userCount: 156,
    features: [
      'تمام امکانات استاندارد',
      'صفحات نامحدود',
      'پشتیبانی تلفنی',
      'گزارشات تحلیلی',
      'چند کاربره',
      'دامنه اختصاصی',
      'SSL رایگان'
    ]
  },
  {
    id: 4,
    name: 'پل سازمانی',
    description: 'راه‌حل کامل برای سازمان‌ها',
    price: 2500000,
    type: 'yearly',
    maxUsers: null,
    status: 'active',
    isPopular: false,
    userCount: 23,
    features: [
      'تمام امکانات حرفه‌ای',
      'کاربران نامحدود',
      'پشتیبانی اختصاصی',
      'مشاوره رایگان',
      'آموزش تیم',
      'API پیشرفته',
      'شخصی‌سازی کامل'
    ]
  },
  {
    id: 5,
    name: 'پل سالانه',
    description: 'تخفیف ویژه اشتراک سالانه',
    price: 1990000,
    type: 'yearly',
    maxUsers: 25,
    status: 'inactive',
    isPopular: false,
    userCount: 0,
    features: [
      'تمام امکانات حرفه‌ای',
      'تخفیف ۵۰ درصدی',
      'پشتیبانی اولویت‌دار',
      'آمار پیشرفته'
    ]
  }
])

// Computed properties
const filteredPlans = computed(() => {
  let filtered = plans.value

  if (searchQuery.value) {
    filtered = filtered.filter(plan =>
      plan.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      plan.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(plan => plan.status === statusFilter.value)
  }

  if (typeFilter.value) {
    filtered = filtered.filter(plan => plan.type === typeFilter.value)
  }

  if (popularFilter.value !== '') {
    const isPopular = popularFilter.value === 'true'
    filtered = filtered.filter(plan => plan.isPopular === isPopular)
  }

  return filtered
})

// Methods
const formatPrice = (price) => {
  if (price === 0) return 'رایگان'
  return new Intl.NumberFormat('fa-IR').format(price)
}

const getPriceUnit = (type) => {
  const units = {
    monthly: 'تومان/ماه',
    yearly: 'تومان/سال',
    lifetime: 'یکبار پرداخت'
  }
  return units[type] || ''
}

const getTypeLabel = (type) => {
  const labels = {
    monthly: 'ماهانه',
    yearly: 'سالانه',
    lifetime: 'دائمی'
  }
  return labels[type] || type
}

const getTypeBadgeClass = (type) => {
  const classes = {
    monthly: 'bg-primary',
    yearly: 'bg-success',
    lifetime: 'bg-info'
  }
  return classes[type] || 'bg-secondary'
}

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

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  typeFilter.value = ''
  popularFilter.value = ''
}

const addFeature = () => {
  newPlan.features.push('')
}

const removeFeature = (index) => {
  newPlan.features.splice(index, 1)
}

const addPlan = () => {
  if (!newPlan.name.trim()) return

  // Filter out empty features
  const cleanFeatures = newPlan.features.filter(feature => feature.trim() !== '')

  const plan = {
    id: plans.value.length + 1,
    ...newPlan,
    features: cleanFeatures,
    userCount: 0
  }

  plans.value.push(plan)

  // Reset form
  Object.assign(newPlan, {
    name: '',
    description: '',
    price: 0,
    type: 'monthly',
    maxUsers: null,
    status: 'active',
    isPopular: false,
    features: ['']
  })

  showAddPlanModal.value = false
  alert('پل با موفقیت اضافه شد!')
}

const editPlan = (plan) => {
  alert(`ویرایش پل: ${plan.name}`)
}

const viewPlan = (plan) => {
  alert(`مشاهده پل: ${plan.name}`)
}

const deletePlan = (plan) => {
  if (confirm(`آیا از حذف پل "${plan.name}" اطمینان دارید؟`)) {
    plans.value = plans.value.filter(p => p.id !== plan.id)
  }
}

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
  title: 'پل‌های کاربری - CMS Panel'
})
</script>

<style scoped>
.plan-card {
  transition: transform 0.2s, box-shadow 0.2s;
  border: 2px solid transparent;
}

.plan-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.plan-popular {
  border-color: #ffc107;
  position: relative;
}

.plan-price {
  font-size: 1.5rem;
  font-weight: bold;
  color: #0d6efd;
  margin-bottom: 0.5rem;
}

.price-amount {
  font-size: 2rem;
}

.price-unit {
  font-size: 0.8rem;
  color: #6c757d;
}

.popular-badge {
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 0.8rem;
}

.features-list {
  flex-grow: 1;
}

.feature-item {
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.plan-stats {
  border-top: 1px solid #dee2e6;
  border-bottom: 1px solid #dee2e6;
  padding: 1rem 0;
  margin: 1rem 0;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: bold;
  color: #0d6efd;
}

.popular-indicator {
  font-size: 1.2rem;
}

.table th {
  font-weight: 600;
  border-bottom: 2px solid #dee2e6;
}
</style>