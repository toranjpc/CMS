<template>
    <div class="user-categorys-page">
      <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <div>
            <h1 class="fw-bold mb-0">دسته بندی محصولات</h1>
            <p class="text-muted mt-1">مدیریت دسته بندی ها و سطح دسترسی‌های محصولات سیستم</p>
            <div v-if="selectedTr1.length > 0 || selectedTr2.length > 0 || selectedTr3.length > 0" class="mt-2">
              <small class="text-primary">
                <i class="fa fa-check-circle me-1"></i>
                مجموع {{ selectedTr1.length + selectedTr2.length + selectedTr3.length }} سطر انتخاب شده
              </small>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button v-if="selectedTr1.length > 0" class="btn btn-outline-danger btn-sm" @click="bulkDeleteSelected1"
              title="حذف گروهی سطح اول">
              <i class="fa fa-trash me-1"></i>
              حذف سطح اول ({{ selectedTr1.length }})
            </button>
            <button v-if="selectedTr2.length > 0" class="btn btn-outline-danger btn-sm" @click="bulkDeleteSelected2"
              title="حذف گروهی سطح دوم">
              <i class="fa fa-trash me-1"></i>
              حذف سطح دوم ({{ selectedTr2.length }})
            </button>
            <button v-if="selectedTr3.length > 0" class="btn btn-outline-danger btn-sm" @click="bulkDeleteSelected3"
              title="حذف گروهی سطح سوم">
              <i class="fa fa-trash me-1"></i>
              حذف سطح سوم ({{ selectedTr3.length }})
            </button>
            <button class="btn btn-primary" @click="opencategoryModal('create')">
              <i class="fa fa-plus-circle me-1"></i>
              افزودن دسته بندی
            </button>
          </div>
        </div>
      </div>
  
      <!-- سه سطح دسته‌بندی -->
      <div class="row">
        <!-- سطح اول -->
        <div class="col-md-4">
      <div class="card mb-4">
            <div class="card-header">
              <h5 class="mb-0">دسته‌بندی‌های اصلی</h5>
            </div>
        <div class="card-body">
              <form @submit.prevent="searchFilters1" class="mb-3">
                <div class="input-group">
                  <input type="text" class="form-control" placeholder="جستجو..." v-model="searchQuery1" />
                  <button class="btn btn-outline-secondary" type="submit">
                    <i class="fa fa-search"></i>
                  </button>
                  <button class="btn btn-outline-warning" type="button" @click="resetFilters1">
                    <i class="fa fa-times"></i>
                  </button>
              </div>
              </form>
              </div>
          </div>
        </div>
  
        <!-- سطح دوم -->
        <div class="col-md-4">
          <div class="card mb-4">
            <div class="card-header">
              <h5 class="mb-0" v-if="selectedParent1">
                زیرمجموعه‌
                {{ getCategoryTitle(selectedParent1) }}
              </h5>
            </div>
            <div class="card-body">
              <form @submit.prevent="searchFilters2" class="mb-3" v-if="selectedParent1">
                <div class="input-group">
                  <input type="text" class="form-control" placeholder="جستجو..." v-model="searchQuery2" />
                  <button class="btn btn-outline-secondary" type="submit">
                  <i class="fa fa-search"></i>
                </button>
                  <button class="btn btn-outline-warning" type="button" @click="resetFilters2">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              </form>
              <p v-else class="text-muted small">ابتدا یک دسته‌بندی اصلی انتخاب کنید</p>
            </div>
          </div>
        </div>
  
        <!-- سطح سوم -->
        <div class="col-md-4">
          <div class="card mb-4">
            <div class="card-header">
              <h5 class="mb-0" v-if="selectedParent2">
                زیرمجموعه‌ {{ getCategoryTitle(selectedParent2) }}
              </h5>
            </div>
            <div class="card-body">
              <form @submit.prevent="searchFilters3" class="mb-3" v-if="selectedParent2">
                <div class="input-group">
                  <input type="text" class="form-control" placeholder="جستجو..." v-model="searchQuery3" />
                  <button class="btn btn-outline-secondary" type="submit">
                    <i class="fa fa-search"></i>
                  </button>
                  <button class="btn btn-outline-warning" type="button" @click="resetFilters3">
                    <i class="fa fa-times"></i>
                  </button>
            </div>
          </form>
              <p v-else class="text-muted small">ابتدا یک زیرمجموعه انتخاب کنید</p>
            </div>
          </div>
        </div>
      </div>
  
      <!-- جداول سه سطح -->
      <div class="row">
        <!-- جدول سطح اول -->
        <div class="col-md-4">
      <div class="card">
            <div class="card-body p-0">
          <!-- Loading State -->
              <div v-if="loading1" class="text-center py-5">
                <div class="spinner-border text-primary spinner-border-sm" role="status">
              <span class="visually-hidden">در حال بارگذاری...</span>
            </div>
          </div>
  
          <!-- Error State -->
              <div v-else-if="error1" class="alert alert-danger m-3" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i>
                {{ error1 }}
                <button class="btn btn-sm btn-outline-danger ms-2" @click="getData1()">
              <i class="fa fa-arrow-clockwise"></i>
              تلاش مجدد
            </button>
          </div>
  
              <!-- Table -->
          <div v-else class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                <tr>
                      <th width="15%">شناسه</th>
                  <th width="">عنوان</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(category, index) in categories1" :key="category.id"
                        :class="getRowClass1(category.id, index)"
                        @click="setCurrentRow(0, index)">
                      <td>
                        <input type="checkbox" :checked="selectedTr1.includes(category.id)"
                          @change="toggleRowSelection1(category.id)" class="form-check-input">
                  </td>
                      <td>
                        <a href="javascript:;" @click="loadChildren1(category.id)" class="text-decoration-none fw-bold">
                          {{ category.title }}
                        </a>
                    <div class="actionBTN btn-group btn-group-sm float-end">
                      <div v-if="category.deleted_at">
                            <button class="btn text-warning btn-sm" @click="openDeleteModal(category, 'restore')"
                              title="بازیافت">
                          <i class="fa fa-refresh"></i>
                        </button>
                            <button class="btn text-danger btn-sm" @click="openDeleteModal(category, 'delete')"
                              title="حذف برای همیشه">
                          <i class="fa fa-times"></i>
                        </button>
                      </div>
                      <div v-else>
                            <button class="btn text-primary btn-sm" @click="editcategory(category)" title="ویرایش">
                          <i class="fa fa-pencil"></i>
                        </button>
                            <button class="btn text-danger btn-sm" @click="openDeleteModal(category)" title="حذف">
                          <i class="fa fa-times"></i>
                        </button>
                      </div>
                    </div>
                  </td>
                    </tr>
                    <tr v-if="categories1.length === 0">
                      <td colspan="2" class="text-center py-4 text-muted">
                        <i class="fa fa-folder-open fa-2x mb-2"></i>
                        <div>هیچ دسته‌بندی یافت نشد</div>
                  </td>
                </tr>
                  </tbody>
                </table>
              </div>
  
              <!-- Pagination -->
              <div class="card-footer" v-if="totalItems1 > itemsPerPage">
                <nav>
                  <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: currentPage1 === 1 }">
                      <a class="page-link" href="javascript:;" @click="getData1(currentPage1 - 1)"
                        v-if="currentPage1 > 1">قبلی</a>
                    </li>
                    <li class="page-item" :class="{ active: page === currentPage1 }" v-for="page in totalPages1"
                      :key="page">
                      <a class="page-link" href="javascript:;" @click="getData1(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage1 === totalPages1 }">
                      <a class="page-link" href="javascript:;" @click="getData1(currentPage1 + 1)"
                        v-if="currentPage1 < totalPages1">بعدی</a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
  
        <!-- جدول سطح دوم -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-body p-0">
              <!-- Loading State -->
              <div v-if="loading2" class="text-center py-5">
                <div class="spinner-border text-primary spinner-border-sm" role="status">
                  <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
              </div>
  
              <!-- Error State -->
              <div v-else-if="error2" class="alert alert-danger m-3" role="alert">
                <i class="fa fa-exclamation-triangle me-2"></i>
                {{ error2 }}
              </div>
  
                <!-- Empty State -->
              <div v-else-if="!selectedParent1" class="text-center py-5 text-muted">
                <i class="fa fa-arrow-left fa-2x mb-3"></i>
                <div>ابتدا یک دسته‌بندی اصلی انتخاب کنید</div>
                    </div>
  
              <!-- Table -->
              <div v-else class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th width="15%">شناسه</th>
                      <th width="">عنوان</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(category, index) in categories2" :key="category.id"
                        :class="getRowClass2(category.id, index)"
                        @click="setCurrentRow(1, index)">
                      <td>
                        <input type="checkbox" :checked="selectedTr2.includes(category.id)"
                          @change="toggleRowSelection2(category.id)" class="form-check-input">
                      </td>
                      <td>
                        <a href="javascript:;" @click="loadChildren2(category.id)" class="text-decoration-none fw-bold">
                          {{ category.title }}
                        </a>
                        <div class="actionBTN btn-group btn-group-sm float-end">
                          <div v-if="category.deleted_at">
                            <button class="btn text-warning btn-sm" @click="openDeleteModal(category, 'restore')"
                              title="بازیافت">
                              <i class="fa fa-refresh"></i>
                            </button>
                            <button class="btn text-danger btn-sm" @click="openDeleteModal(category, 'delete')"
                              title="حذف برای همیشه">
                              <i class="fa fa-times"></i>
                            </button>
                          </div>
                          <div v-else>
                            <button class="btn text-primary btn-sm" @click="editcategory(category)" title="ویرایش">
                              <i class="fa fa-pencil"></i>
                            </button>
                            <button class="btn text-danger btn-sm" @click="openDeleteModal(category)" title="حذف">
                              <i class="fa fa-times"></i>
                            </button>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="categories2.length === 0">
                      <td colspan="2" class="text-center py-4 text-muted">
                        <i class="fa fa-folder-open fa-2x mb-2"></i>
                        <div>هیچ زیرمجموعه‌ای یافت نشد</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
  
          <!-- Pagination -->
              <div class="card-footer" v-if="totalItems2 > itemsPerPage">
                <nav>
                  <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: currentPage2 === 1 }">
                      <a class="page-link" href="javascript:;" @click="getData2(selectedParent1, currentPage2 - 1)"
                        v-if="currentPage2 > 1">قبلی</a>
                    </li>
                    <li class="page-item" :class="{ active: page === currentPage2 }" v-for="page in totalPages2"
                      :key="page">
                      <a class="page-link" href="javascript:;" @click="getData2(selectedParent1, page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage2 === totalPages2 }">
                      <a class="page-link" href="javascript:;" @click="getData2(selectedParent1, currentPage2 + 1)"
                        v-if="currentPage2 < totalPages2">بعدی</a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
  
        <!-- جدول سطح سوم -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-body p-0">
              <!-- Loading State -->
              <div v-if="loading3" class="text-center py-5">
                <div class="spinner-border text-primary spinner-border-sm" role="status">
                  <span class="visually-hidden">در حال بارگذاری...</span>
            </div>
              </div>
  
              <!-- Error State -->
              <div v-else-if="error3" class="alert alert-danger m-3" role="alert">
                <i class="fa fa-exclamation-triangle me-2"></i>
                {{ error3 }}
              </div>
  
              <!-- Empty State -->
              <div v-else-if="!selectedParent2" class="text-center py-5 text-muted">
                <i class="fa fa-arrow-left fa-2x mb-3"></i>
                <div>ابتدا یک زیرمجموعه انتخاب کنید</div>
              </div>
  
              <!-- Table -->
              <div v-else class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th width="15%">شناسه</th>
                      <th width="">عنوان</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(category, index) in categories3" :key="category.id"
                        :class="getRowClass3(category.id, index)"
                        @click="setCurrentRow(2, index)">
                      <td>
                        <input type="checkbox" :checked="selectedTr3.includes(category.id)"
                          @change="toggleRowSelection3(category.id)" class="form-check-input">
                      </td>
                      <td>
                        {{ category.title }}
                        <div class="actionBTN btn-group btn-group-sm float-end">
                          <div v-if="category.deleted_at">
                            <button class="btn text-warning btn-sm" @click="openDeleteModal(category, 'restore')"
                              title="بازیافت">
                              <i class="fa fa-refresh"></i>
                            </button>
                            <button class="btn text-danger btn-sm" @click="openDeleteModal(category, 'delete')"
                              title="حذف برای همیشه">
                              <i class="fa fa-times"></i>
                            </button>
                          </div>
                          <div v-else>
                            <button class="btn text-primary btn-sm" @click="editcategory(category)" title="ویرایش">
                              <i class="fa fa-pencil"></i>
                            </button>
                            <button class="btn text-danger btn-sm" @click="openDeleteModal(category)" title="حذف">
                              <i class="fa fa-times"></i>
                            </button>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="categories3.length === 0">
                      <td colspan="2" class="text-center py-4 text-muted">
                        <i class="fa fa-folder-open fa-2x mb-2"></i>
                        <div>هیچ زیرمجموعه‌ای یافت نشد</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
  
              <!-- Pagination -->
              <div class="card-footer" v-if="totalItems3 > itemsPerPage">
            <nav>
                  <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: currentPage3 === 1 }">
                      <a class="page-link" href="javascript:;" @click="getData3(selectedParent2, currentPage3 - 1)"
                        v-if="currentPage3 > 1">قبلی</a>
                </li>
                    <li class="page-item" :class="{ active: page === currentPage3 }" v-for="page in totalPages3"
                      :key="page">
                      <a class="page-link" href="javascript:;" @click="getData3(selectedParent2, page)">{{ page }}</a>
                </li>
                    <li class="page-item" :class="{ disabled: currentPage3 === totalPages3 }">
                      <a class="page-link" href="javascript:;" @click="getData3(selectedParent2, currentPage3 + 1)"
                        v-if="currentPage3 < totalPages3">بعدی</a>
                </li>
              </ul>
            </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- category Modal (Create/Edit/View) -->
      <div class="modal fade" :class="{ show: showcategoryModal }"
        :style="{ display: showcategoryModal ? 'block' : 'none' }" tabindex="-1">
        <div class="shadow" @click="showcategoryModal = false; currentcategory = null"></div>
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header bg-info text-white">
              <h5 class="modal-title">
                <span v-if="modalMode === 'create'">افزودن دسته بندی جدید</span>
                <span v-else-if="modalMode === 'edit'">ویرایش دسته بندی</span>
                <span v-else-if="modalMode === 'view'">مشاهده دسته بندی</span>
              </h5>
              <button type="button" class="btn-close" @click="showcategoryModal = false; currentcategory = null"></button>
            </div>
            <div class="modal-body">
              <div class="card-title">
                <div v-if="formError" class="alert alert-danger">{{ formError }}</div>
              </div>
  
              <form @submit.prevent="modalMode !== 'view' ? savecategory() : null" v-if="currentcategory">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">نام دسته بندی *</label>
                    <input type="text" class="form-control" v-model="currentcategory.title"
                      :readonly="modalMode === 'view'" ref="categoryTitleInput" required />
                  </div>
                  <div class="col-12" v-if="modalMode !== 'view'">
                    <label class="form-label">دسته بندی والد</label>
                    <select class="form-select" v-model="currentcategory.parent_id">
                      <option :value="null">بدون والد (دسته اصلی)</option>
                      <option v-for="category in availableParents" :key="category.id" :value="category.id">
                        {{ category.title }}
                      </option>
                    </select>
                    <div class="form-text">
                      انتخاب کنید که این دسته بندی زیرمجموعه کدام دسته باشد
                          </div>
                        </div>
                  <div class="col-12" v-else-if="currentcategory.parent_id">
                    <label class="form-label">دسته بندی والد</label>
                    <input type="text" class="form-control" :value="getParentName(currentcategory.parent_id)" readonly />
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer" v-if="formloading">
              درحال بارگذاری ....
              <div class="spinner-border btn btn-secondary" category="status"></div>
            </div>
            <div class="modal-footer" v-else-if="modalMode !== 'view'">
              <button type="button" class="btn btn-secondary"
                @click="showcategoryModal = false; currentcategory = null">انصراف</button>
              <button type="button" class="btn btn-primary" @click="savecategory">
                <span v-if="modalMode === 'create'">افزودن دسته بندی</span>
                <span v-else-if="modalMode === 'edit'">به‌روزرسانی دسته بندی</span>
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
    title: 'دسته بندی محصولات'
  })
  const { $api } = useNuxtApp()
  
  // داده‌های سه سطح دسته‌بندی
  const categories1 = ref([]) // سطح اول (والدین اصلی)
  const categories2 = ref([]) // سطح دوم (فرزندان سطح اول)
  const categories3 = ref([]) // سطح سوم (فرزندان سطح دوم)
  
  // وضعیت بارگذاری برای هر سطح
  const loading1 = ref(false)
  const loading2 = ref(false)
  const loading3 = ref(false)
  
  // خطا برای هر سطح
  const error1 = ref(null)
  const error2 = ref(null)
  const error3 = ref(null)
  
  // pagination برای هر سطح
  const totalItems1 = ref(0)
  const totalPages1 = ref(0)
  const currentPage1 = ref(1)
  
  const totalItems2 = ref(0)
  const totalPages2 = ref(0)
  const currentPage2 = ref(1)
  
  const totalItems3 = ref(0)
  const totalPages3 = ref(0)
  const currentPage3 = ref(1)
  
  const itemsPerPage = ref(10)
  
  // جستجو برای هر سطح
  const searchQuery1 = ref('')
  const searchQuery2 = ref('')
  const searchQuery3 = ref('')
  
  // انتخاب سطرها برای هر سطح
  const selectedTr1 = ref([])
  const selectedTr2 = ref([])
  const selectedTr3 = ref([])
  
  // شاخص سطر فعلی برای هر سطح
  const currentRowIndex1 = ref(-1)
  const currentRowIndex2 = ref(-1)
  const currentRowIndex3 = ref(-1)
  
  // شاخص جدول فعلی (برای حرکت با کلیدهای چپ/راست)
  const currentTableIndex = ref(0)
  
  // ID والد انتخاب شده برای هر سطح
  const selectedParent1 = ref(null) // برای سطح 2
  const selectedParent2 = ref(null) // برای سطح 3
  
  const formloading = ref(false)
  const formError = ref(null)
  
  const showcategoryModal = ref(false)
  const modalMode = ref('create') /* 'create', 'edit', 'view' */
  const currentcategory = ref(null)
  const categoryTitleInput = ref(null)
  const availableParents = ref([])
  
  // تابع بارگذاری داده‌ها برای سطح اول (والدین اصلی)
  const getData1 = async (page = 1, search = '') => {
    loading1.value = true
    error1.value = null
  
    try {
      let url = `/users/categories?limit=${itemsPerPage.value}&page=${page}`
      if (search.trim()) url += `&title=${encodeURIComponent(search)}`
  
      console.log('Requesting URL:', url)
      const response = await $api(url)
      console.log('API Response for level 1:', response)
  
      // اطمینان از اینکه داده‌ها همیشه آرایه هستند
      const data = response?.data || {}
      categories1.value = Array.isArray(data.items) ? data.items : []
      totalItems1.value = data.total || 0
      totalPages1.value = data.last_page || 0
      currentPage1.value = data.current_page || 1
  
      selectedTr1.value = []
      currentRowIndex1.value = -1 // هیچ سطری انتخاب نشده
  
    } catch (err) {
      console.error('Error loading categories1:', err)
      console.error('Error details:', err.response?.data || err.message)
      error1.value = 'خطا در بارگذاری لیست دسته‌بندی‌ها'
      // در صورت خطا، آرایه‌ها را خالی کنیم
      categories1.value = []
      totalItems1.value = 0
      totalPages1.value = 0
      currentPage1.value = 1
    } finally {
      loading1.value = false
    }
  }
  
  // تابع بارگذاری داده‌ها برای سطح دوم (فرزندان سطح اول)
  const getData2 = async (parentId, page = 1, search = '') => {
    if (!parentId) return
  
    loading2.value = true
    error2.value = null
  
    try {
      let url = `/users/categories?limit=${itemsPerPage.value}&page=${page}&father=${parentId}`
      if (search.trim()) url += `&title=${encodeURIComponent(search)}`
  
      const response = await $api(url)
  
      // اطمینان از اینکه داده‌ها همیشه آرایه هستند
      const data = response?.data || {}
      categories2.value = Array.isArray(data.items) ? data.items : []
      totalItems2.value = data.total || 0
      totalPages2.value = data.last_page || 0
      currentPage2.value = data.current_page || 1
  
      selectedTr2.value = []
      currentRowIndex2.value = -1 // هیچ سطری انتخاب نشده
      selectedParent1.value = parentId
  
    } catch (err) {
      console.error('Error loading categories2:', err)
      error2.value = 'خطا در بارگذاری فرزندان'
      // در صورت خطا، آرایه‌ها را خالی کنیم
      categories2.value = []
      totalItems2.value = 0
      totalPages2.value = 0
      currentPage2.value = 1
    } finally {
      loading2.value = false
    }
  }
  
  // تابع بارگذاری داده‌ها برای سطح سوم (فرزندان سطح دوم)
  const getData3 = async (parentId, page = 1, search = '') => {
    if (!parentId) return
  
    loading3.value = true
    error3.value = null
  
    try {
      let url = `/users/categories?limit=${itemsPerPage.value}&page=${page}&father=${parentId}`
      if (search.trim()) url += `&title=${encodeURIComponent(search)}`
  
      const response = await $api(url)
  
      // اطمینان از اینکه داده‌ها همیشه آرایه هستند
      const data = response?.data || {}
      categories3.value = Array.isArray(data.items) ? data.items : []
      totalItems3.value = data.total || 0
      totalPages3.value = data.last_page || 0
      currentPage3.value = data.current_page || 1
  
      selectedTr3.value = []
      currentRowIndex3.value = -1 // هیچ سطری انتخاب نشده
      selectedParent2.value = parentId
  
    } catch (err) {
      console.error('Error loading categories3:', err)
      error3.value = 'خطا در بارگذاری فرزندان'
      // در صورت خطا، آرایه‌ها را خالی کنیم
      categories3.value = []
      totalItems3.value = 0
      totalPages3.value = 0
      currentPage3.value = 1
    } finally {
      loading3.value = false
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
        case 'ArrowLeft':
          event.preventDefault()
          navigateTables(1) // حرکت به سمت راست
          break
        case 'ArrowRight':
          event.preventDefault()
          navigateTables(-1) // حرکت به سمت چپ
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
    const currentTable = currentTableIndex.value
    let currentIndex, totalRows, tableData
  
    switch (currentTable) {
      case 0:
        currentIndex = currentRowIndex1
        tableData = categories1
        break
      case 1:
        currentIndex = currentRowIndex2
        tableData = categories2
        break
      case 2:
        currentIndex = currentRowIndex3
        tableData = categories3
        break
    }
  
    totalRows = tableData.value.length
    if (totalRows === 0) return
  
    // اگر هیچ سطری انتخاب نشده، اولین حرکت کاربر را به اولین سطر ببر
    if (currentIndex.value === -1) {
      currentIndex.value = direction > 0 ? 0 : totalRows - 1
      return
    }
  
    // حرکت به سطر بعدی/قبلی
    currentIndex.value += direction
  
    // محدود کردن به محدوده مجاز
    if (currentIndex.value < 0) {
      currentIndex.value = 0
    } else if (currentIndex.value >= totalRows) {
      currentIndex.value = totalRows - 1
    }
  }
  
  const navigateTables = (direction) => {
    const totalTables = 3
    currentTableIndex.value += direction
  
    // محدود کردن به محدوده مجاز
    if (currentTableIndex.value < 0) {
      currentTableIndex.value = 0
    } else if (currentTableIndex.value >= totalTables) {
      currentTableIndex.value = totalTables - 1
    }
  
    // ریست کردن شاخص سطر برای جدول جدید
    switch (currentTableIndex.value) {
      case 0:
        if (categories1.value.length > 0) {
          currentRowIndex1.value = 0
        }
        break
      case 1:
        if (categories2.value.length > 0) {
          currentRowIndex2.value = 0
        }
        break
      case 2:
        if (categories3.value.length > 0) {
          currentRowIndex3.value = 0
        }
        break
    }
  }
  
  const toggleCurrentRowSelection = () => {
    const currentTable = currentTableIndex.value
    let currentIndex, tableData, selectedItems
  
    switch (currentTable) {
      case 0:
        currentIndex = currentRowIndex1
        tableData = categories1
        selectedItems = selectedTr1
        break
      case 1:
        currentIndex = currentRowIndex2
        tableData = categories2
        selectedItems = selectedTr2
        break
      case 2:
        currentIndex = currentRowIndex3
        tableData = categories3
        selectedItems = selectedTr3
        break
    }
  
    if (currentIndex.value === -1 || !tableData.value[currentIndex.value]) return
  
    const categoryId = tableData.value[currentIndex.value].id
    toggleRowSelection(selectedItems, categoryId)
  }
  
  const performCurrentRowAction = () => {
    const currentTable = currentTableIndex.value
    let currentIndex, tableData
  
    switch (currentTable) {
      case 0:
        currentIndex = currentRowIndex1
        tableData = categories1
        break
      case 1:
        currentIndex = currentRowIndex2
        tableData = categories2
        break
      case 2:
        currentIndex = currentRowIndex3
        tableData = categories3
        break
    }
  
    if (currentIndex.value === -1 || !tableData.value[currentIndex.value]) return
  
    const category = tableData.value[currentIndex.value]
  
    // برای جدول اول، فرزندان را بارگذاری کن
    if (currentTable === 0) {
      loadChildren1(category.id)
    }
    // برای جدول دوم، فرزندان سطح سوم را بارگذاری کن
    else if (currentTable === 1) {
      loadChildren2(category.id)
    }
  }
  
  const newcategory = reactive({
    title: '',
    parent_id: null
  })
  
  
  onMounted(() => {
    getData1() // بارگذاری سطح اول
    setupKeyboardNavigation()
  })
  
  // Keyboard navigation setup
  // سیستم ناوبری کیبورد فعلاً غیرفعال است
  
  
  // تابع کمکی برای گرفتن کلاس سطر (دیگر استفاده نمی‌شود)
  
  // حذف گروهی برای سطح اول
  const bulkDeleteSelected1 = async () => {
    if (selectedTr1.value.length === 0) return
  
    const result = await Swal.fire({
      title: 'تأیید حذف گروهی',
      text: `آیا مطمئن هستید که می‌خواهید ${selectedTr1.value.length} دسته‌بندی اصلی انتخاب شده را حذف کنید؟`,
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
  
    await performBulkDelete(selectedTr1, categories1, () => getData1())
  }
  
  // حذف گروهی برای سطح دوم
  const bulkDeleteSelected2 = async () => {
    if (selectedTr2.value.length === 0) return
  
    const result = await Swal.fire({
      title: 'تأیید حذف گروهی',
      text: `آیا مطمئن هستید که می‌خواهید ${selectedTr2.value.length} زیرمجموعه انتخاب شده را حذف کنید؟`,
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
  
    await performBulkDelete(selectedTr2, categories2, () => selectedParent1.value && getData2(selectedParent1.value))
  }
  
  // حذف گروهی برای سطح سوم
  const bulkDeleteSelected3 = async () => {
    if (selectedTr3.value.length === 0) return
  
    const result = await Swal.fire({
      title: 'تأیید حذف گروهی',
      text: `آیا مطمئن هستید که می‌خواهید ${selectedTr3.value.length} زیرمجموعه سطح سوم انتخاب شده را حذف کنید؟`,
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
  
    await performBulkDelete(selectedTr3, categories3, () => selectedParent2.value && getData3(selectedParent2.value))
  }
  
  // تابع کمکی برای حذف گروهی
  const performBulkDelete = async (selectedItems, categoriesArray, refreshCallback) => {
    formloading.value = true
    let successCount = 0
    let errorCount = 0
  
    for (const categoryId of selectedItems.value) {
      try {
        await $api(`/users/categories/${categoryId}`, {
          method: 'DELETE'
        })
        successCount++
      } catch (err) {
        console.error(`Error deleting category ${categoryId}:`, err)
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
        text: `${successCount} دسته‌بندی با موفقیت حذف شد.`,
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
        text: `${successCount} دسته‌بندی حذف شد. ${errorCount} دسته‌بندی با خطا مواجه شد.`,
        icon: 'warning',
        customClass: {
          popup: 'swal-rtl'
        }
      })
    }
  
    formloading.value = false
  }
  
  // تابع عمومی برای انتخاب سطرها
  const toggleRowSelection = (selectedItems, categoryId) => {
    const index = selectedItems.value.indexOf(categoryId)
    if (index > -1) {
      selectedItems.value.splice(index, 1)
    } else {
      selectedItems.value.push(categoryId)
    }
  }
  
  // توابع انتخاب سطرها برای هر سطح
  const toggleRowSelection1 = (categoryId) => {
    toggleRowSelection(selectedTr1, categoryId)
  }
  
  const toggleRowSelection2 = (categoryId) => {
    toggleRowSelection(selectedTr2, categoryId)
  }
  
  const toggleRowSelection3 = (categoryId) => {
    toggleRowSelection(selectedTr3, categoryId)
  }
  
  const getRowClass1 = (categoryId, index) => {
    const classes = []
    if (selectedTr1.value.includes(categoryId)) classes.push('selected')
    if (index === currentRowIndex1.value && currentTableIndex.value === 0) classes.push('current-row')
    return classes.join(' ')
  }
  
  const getRowClass2 = (categoryId, index) => {
    const classes = []
    if (selectedTr2.value.includes(categoryId)) classes.push('selected')
    if (index === currentRowIndex2.value && currentTableIndex.value === 1) classes.push('current-row')
    return classes.join(' ')
  }
  
  const getRowClass3 = (categoryId, index) => {
    const classes = []
    if (selectedTr3.value.includes(categoryId)) classes.push('selected')
    if (index === currentRowIndex3.value && currentTableIndex.value === 2) classes.push('current-row')
    return classes.join(' ')
  }
  
  const setCurrentRow = (tableIndex, rowIndex) => {
    currentTableIndex.value = tableIndex
    switch (tableIndex) {
      case 0:
        currentRowIndex1.value = rowIndex
        break
      case 1:
        currentRowIndex2.value = rowIndex
        break
      case 2:
        currentRowIndex3.value = rowIndex
        break
    }
  }
  
  // بارگذاری فرزندان سطح اول
  const loadChildren1 = (parentId) => {
    categories2.value = [] // پاک کردن سطح دوم
    categories3.value = [] // پاک کردن سطح سوم
    selectedParent2.value = null
    getData2(parentId)
  }
  
  // بارگذاری فرزندان سطح دوم
  const loadChildren2 = (parentId) => {
    categories3.value = [] // پاک کردن سطح سوم
    getData3(parentId)
  }
  
  // گرفتن نام دسته‌بندی بر اساس ID
  const getCategoryTitle = (categoryId) => {
    // جستجو در تمام سطوح
    const allCategories = [...categories1.value, ...categories2.value, ...categories3.value]
    const category = allCategories.find(cat => cat.id == categoryId)
    return category ? category.title : 'نامشخص'
  }
  
  // گرفتن سطح دسته‌بندی بر اساس ID
  const getCategoryLevel = (categoryId) => {
    if (categories1.value.some(cat => cat.id == categoryId)) return 1
    if (categories2.value.some(cat => cat.id == categoryId)) return 2
    if (categories3.value.some(cat => cat.id == categoryId)) return 3
    return 0
  }
  
  const getParentName = (parentId) => {
    if (!parentId) return 'بدون والد'
    return getCategoryTitle(parentId)
  }
  
  const formatDate = (dateString) => {
    if (!dateString) return '-'
    let dateinit = new Date(dateString)
    let date = dateinit.toLocaleDateString('fa-IR')
    let time = dateinit.toLocaleTimeString('fa-IR')
    return `${date} <span class="text-muted">${time}</span>`
  }
  
  const searchFilters1 = () => {
    getData1(1, searchQuery1.value)
  }
  
  const searchFilters2 = () => {
    if (selectedParent1.value) {
      getData2(selectedParent1.value, 1, searchQuery2.value)
    }
  }
  
  const searchFilters3 = () => {
    if (selectedParent2.value) {
      getData3(selectedParent2.value, 1, searchQuery3.value)
    }
  }
  
  const resetFilters1 = () => {
    searchQuery1.value = ''
    selectedTr1.value = []
    currentRowIndex1.value = -1
    getData1()
  }
  
  const resetFilters2 = () => {
    searchQuery2.value = ''
    selectedTr2.value = []
    currentRowIndex2.value = -1
    if (selectedParent1.value) {
      getData2(selectedParent1.value)
    }
  }
  
  const resetFilters3 = () => {
    searchQuery3.value = ''
    selectedTr3.value = []
    currentRowIndex3.value = -1
    if (selectedParent2.value) {
      getData3(selectedParent2.value)
    }
  }
  
  
  const savecategory = async () => {
    if (!currentcategory.value.title.trim()) {
      formError.value = 'عنوان دسته بندی الزامی است'
      return
    }
  
    formloading.value = true
    formError.value = null
  
    try {
      const categoryData = {
        title: currentcategory.value.title,
        parent_id: currentcategory.value.parent_id || null,
      }
  
      let response
      if (modalMode.value === 'create') {
        response = await $api('/users/categories', {
          method: 'POST',
          body: categoryData
        })
  
        if (response?.data) {
          const newCategory = response.data
          // اضافه کردن به سطح مناسب بر اساس parent_id
          if (!newCategory.f_id || newCategory.f_id == 0) {
            // اضافه به سطح اول
            categories1.value.unshift(newCategory)
          } else {
            // بررسی سطح والد و اضافه کردن به سطح بعدی
            const parentLevel = getCategoryLevel(newCategory.f_id)
            if (parentLevel === 1) {
              categories2.value.unshift(newCategory)
            } else if (parentLevel === 2) {
              categories3.value.unshift(newCategory)
            }
          }
        }
      } else if (modalMode.value === 'edit') {
        response = await $api(`/users/categories/${currentcategory.value.id}`, {
          method: 'PUT',
          body: categoryData
        })
  
        if (response?.data) {
          const updatedCategory = response.data
  
          // حذف از سطح فعلی
          [categories1, categories2, categories3].forEach(categories => {
            const index = categories.value.findIndex(cat => cat.id === updatedCategory.id)
          if (index !== -1) {
              categories.value.splice(index, 1)
            }
          })
  
          // اضافه کردن به سطح جدید بر اساس parent_id
          if (!updatedCategory.f_id || updatedCategory.f_id == 0) {
            categories1.value.unshift(updatedCategory)
          } else {
            const parentLevel = getCategoryLevel(updatedCategory.f_id)
            if (parentLevel === 1) {
              categories2.value.unshift(updatedCategory)
            } else if (parentLevel === 2) {
              categories3.value.unshift(updatedCategory)
            }
          }
  
          // بروزرسانی فرزندان اگر والد تغییر کرده
          if (selectedParent1.value === currentcategory.value.id) {
            await getData2(updatedCategory.id)
          }
          if (selectedParent2.value === currentcategory.value.id) {
            await getData3(updatedCategory.id)
          }
        }
      }
  
      showcategoryModal.value = false
      currentcategory.value = null
  
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
  
  
  const opencategoryModal = async (mode = 'create', category = null) => {
    modalMode.value = mode
  
    if (mode === 'create') {
      currentcategory.value = {
        title: '',
        parent_id: null
      }
    } else if (category) {
      currentcategory.value = { ...category }
      // تنظیم parent_id بر اساس f_id (اگر 0 یا null باشد، null می‌شود)
      currentcategory.value.parent_id = (category.f_id && category.f_id != 0) ? category.f_id : null
    }
  
    // بروزرسانی لیست والدین ممکن (تمام دسته‌ها از تمام سطوح)
    const allCategories = [...categories1.value, ...categories2.value, ...categories3.value]
    availableParents.value = allCategories.filter(cat => !currentcategory.value || cat.id !== currentcategory.value.id)
  
    showcategoryModal.value = true
  
    // فوکوس روی فیلد نام دسته بندی در حالت ایجاد
    if (mode === 'create') {
      await nextTick()
      if (categoryTitleInput.value) {
        categoryTitleInput.value.focus()
      }
    }
  }
  
  const editcategory = (category) => {
    formError.value = null
    opencategoryModal('edit', category)
  }
  
  const viewcategory = (category) => {
    formError.value = null
    opencategoryModal('view', category)
  }
  
  
  
  const openDeleteModal = async (category, method = '') => {
    let title, text, icon, confirmButtonText, confirmButtonColor
  
    if (method === 'restore') {
      title = 'بازیابی دسته بندی'
      text = `آیا می‌خواهید دسته بندی "${category.title}" را بازیابی کنید؟`
      icon = 'question'
      confirmButtonText = 'بله، بازیابی کن'
      confirmButtonColor = '#28a745'
    } else if (method === 'delete') {
      title = 'حذف کامل دسته بندی'
      text = `آیا مطمئن هستید که می‌خواهید دسته بندی "${category.title}" را برای همیشه حذف کنید؟ این عمل قابل برگشت نیست!`
      icon = 'error'
      confirmButtonText = 'بله، برای همیشه حذف کن'
      confirmButtonColor = '#dc3545'
    } else {
      title = 'حذف دسته بندی'
      text = `آیا می‌خواهید دسته بندی "${category.title}" را حذف کنید؟`
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
      await confirmDelete(category, method)
    }
  }
  
  const confirmDelete = async (category, method = '') => {
    formloading.value = true
    error.value = null
  
    try {
      let url, httpMethod, successMessage
  
      if (method === 'restore') {
        url = `/users/categories/${category.id}/restore`
        httpMethod = 'PATCH'
        successMessage = 'دسته بندی با موفقیت بازیابی شد!'
      } else if (method === 'delete') {
        url = `/users/categories/${category.id}/force`
        httpMethod = 'DELETE'
        successMessage = 'دسته بندی برای همیشه حذف شد!'
      } else {
        url = `/users/categories/${category.id}`
        httpMethod = 'DELETE'
        successMessage = 'دسته بندی با موفقیت حذف شد!'
      }
  
      await $api(url, {
        method: httpMethod
      })
  
      // حذف از تمام سطوح
      [categories1, categories2, categories3].forEach(categories => {
        const filtered = categories.value.filter(cat => cat.id !== category.id)
        categories.value = filtered
      })
  
      // پاک کردن فرزندان اگر والد حذف شده
      if (selectedParent1.value === category.id) {
        categories2.value = []
        selectedParent1.value = null
      }
      if (selectedParent2.value === category.id) {
        categories3.value = []
        selectedParent2.value = null
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
      console.error('Error deleting category:', err)
      const status = err?.response?.status
      const data = err?.response?._data
      let errorMessage = 'خطا در عملیات'
  
      if (status === 404) {
        errorMessage = 'دسته بندی یافت نشد'
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
        }   ``
      })
    } finally {
      formloading.value = false
    }
  }
  
  </script>
  
  <style scoped>
    
  </style>