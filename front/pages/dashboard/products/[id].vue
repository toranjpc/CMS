<template>
  <div class="product-add-page">
    <div class="row mb-4">
      <div class="col-12">
        <h1 class="fw-bold mb-0">
          <span v-if="isAdd">ایجاد محصول جدید</span>
          <span v-else-if="isEdit">ویرایش محصول | {{ product.title || '...' }}</span>
          <span v-else-if="isView">مشاهده محصول | {{ product.title || '...' }}</span>
        </h1>
      </div>
    </div>

    <form @submit.prevent="saveProduct('add')" @keydown.enter.prevent enctype="multipart/form-data" id="productForm">
      <div class="row">
        <div class="col-md-9">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span>اطلاعات محصول</span>
              <div class="d-flex gap-2">
                <!-- دکمه‌های مدیریت برای حالت view -->
                <template v-if="isView">
                  <button type="button" class="btn btn-primary" @click="goToEdit">
                    <i class="fa fa-pencil me-1"></i>
                    ویرایش
                  </button>
                  <button type="button" class="btn btn-danger" @click="deleteProduct">
                    <i class="fa fa-times me-1"></i>
                    حذف
                  </button>
                </template>

                <!-- دکمه‌های مدیریت برای حالت add/edit -->
                <template v-else>
                  <select class="form-select form-select-sm" v-model="product.status" style="width: auto;"
                    :disabled="isView">
                    <option :value="2">منتشر نشده</option>
                    <option :value="1">انتشار</option>
                  </select>
                  <div class="btn-group">
                    <button type="submit" class="btn btn-info" :disabled="formloading || isView">
                      <i class="fa fa-save me-1"></i>
                      ذخیره اطلاعات
                    </button>
                    <button type="button" class="btn btn-info dropdown-toggle dropdown-toggle-split"
                      data-bs-toggle="dropdown" :disabled="isView"></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <button type="button" class="dropdown-item" @click="saveAndNew"
                          :disabled="formloading || isView">
                          <i class="fa fa-plus-square me-1"></i>
                          ذخیره و جدید
                        </button>
                      </li>
                      <li>
                        <button type="button" class="dropdown-item" @click="saveAndExit"
                          :disabled="formloading || isView">
                          <i class="fa fa-mail-forward me-1"></i>
                          ذخیره و خروج
                        </button>
                      </li>
                      <li v-if="isEdit">
                        <hr class="dropdown-divider">
                      </li>
                      <li v-if="isEdit && product.status > 0">
                        <button type="button" class="dropdown-item text-danger" @click="deleteProduct">
                          <i class="fa fa-times me-1"></i>
                          حذف محصول
                        </button>
                      </li>
                      <li v-else-if="isEdit && product.status === 0">
                        <button type="button" class="dropdown-item text-warning" @click="restoreProduct">
                          <i class="fa fa-refresh me-1"></i>
                          بازیابی محصول
                        </button>
                        <button type="button" class="dropdown-item text-danger" @click="forceDeleteProduct">
                          <i class="fa fa-times me-1"></i>
                          حذف برای همیشه
                        </button>
                      </li>
                    </ul>
                  </div>
                </template>
              </div>
            </div>

            <div class="card-body">
              <div v-if="formError" class="alert alert-danger">{{ formError }}</div>

              <!-- دسته‌بندی‌ها -->
              <div class="row g-3 mb-4">
                <div class="col-12">
                  <label class="form-label">دسته‌بندی محصول</label>
                  <div class="row g-3">
                    <!-- ستون اول: دسته‌بندی‌های اصلی -->
                    <div class="col-md-4">
                      <label class="form-label small">دسته‌بندی اصلی
                        <span v-if="selectedMainCategory"> : {{ selectedMainCategory.title || selectedMainCategory
                        }}</span>
                      </label>
                      <widgets.searchinput v-model="selectedMainCategory" placeholder="دسته‌بندی اصلی"
                        textSearchUrl="/products/categories/list" idSearchUrl="/products/categories/" methode="GET"
                        :columns="[{ label: 'عنوان', key: 'title' }]" :disabled="isView" />
                    </div>

                    <!-- ستون دوم: دسته‌بندی‌های فرزند -->
                    <div class="col-md-4">
                      <label class="form-label small">دسته‌بندی فرزند
                        <span v-if="selectedSubCategory"> : {{ selectedSubCategory.title || selectedSubCategory
                        }}</span>
                      </label>
                      <widgets.searchinput v-model="selectedSubCategory" placeholder="دسته‌بندی فرزند"
                        textSearchUrl="/products/categories/list" idSearchUrl="/products/categories/"
                        :querySearch="{ father: selectedMainCategory ? selectedMainCategory.id : '' }" methode="GET"
                        :columns="[{ label: 'عنوان', key: 'title' }]"
                        :disabled="!selectedMainCategory || !selectedMainCategory.id" />
                    </div>

                    <!-- ستون سوم: دسته‌بندی‌های فرزند دوم -->
                    <div class="col-md-4">
                      <label class="form-label small">دسته‌بندی فرزند دوم
                        <span v-if="selectedSubSubCategory"> : {{ selectedSubSubCategory.title || selectedSubSubCategory
                          }}</span>
                      </label>
                      <widgets.searchinput v-model="selectedSubSubCategory" placeholder="دسته‌بندی فرزند دوم"
                        textSearchUrl="/products/categories/list" idSearchUrl="/products/categories/"
                        :querySearch="{ father: selectedSubCategory ? selectedSubCategory.id : '' }" methode="GET"
                        :columns="[{ label: 'عنوان', key: 'title' }]"
                        :disabled="!selectedSubCategory || !selectedSubCategory.id" />
                    </div>
                  </div>




                </div>
              </div>

              <div class="row g-3">

                <div class="col-md-4">
                  <label class="form-label small">واحد اندازه گیری
                    <span v-if="selectedUnit"> : {{ selectedUnit.title || selectedUnit }}</span>
                  </label>
                  <widgets.searchinput v-model="selectedUnit" placeholder="واحد اندازه گیری"
                    textSearchUrl="/products/units/list" idSearchUrl="/products/units/" methode="GET"
                    :columns="[{ label: 'عنوان', key: 'title' }]" :disabled="isView" />
                </div>

                <div class="col-md-4">
                  <label class="form-label small">برند
                    <span v-if="selectedBrand"> : {{ selectedBrand.title || selectedBrand }}</span>
                  </label>
                  <widgets.searchinput v-model="selectedBrand" placeholder="برند" textSearchUrl="/products/brands/list"
                    idSearchUrl="/products/brands/" methode="GET" :columns="[{ label: 'عنوان', key: 'title' }]"
                    :disabled="isView" />
                </div>

                <div class="col-md-4">
                  <label class="form-label small">انبار
                    <span v-if="selectedWarehouse"> : {{ selectedWarehouse.title || selectedWarehouse }}</span>
                  </label>
                  <widgets.searchinput v-model="selectedWarehouse" placeholder="انبار"
                    textSearchUrl="/products/warehouses/list" idSearchUrl="/products/warehouses/" methode="GET"
                    :columns="[{ label: 'عنوان', key: 'title' }]" :disabled="isView" />
                </div>



                <!-- عنوان -->
                <div class="col-md-6">
                  <label class="form-label">عنوان *</label>
                  <input type="text" class="form-control" v-model="product.title" placeholder="عنوان محصول"
                    :readonly="isView" :required="!isView" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">شناسه اختصاصی (بارکد) *</label>
                  <input type="text" class="form-control" v-model="product.barcode"
                    placeholder="شناسه اختصاصی (بارکد) محصول" :readonly="isView" :required="!isView" />
                </div>
                <div class="col-md-3">
                  <label class="form-label">نرخ مالیات % *</label>
                  <input type="number" min="0" max="100" class="form-control" v-model="product.tax_rate"
                    placeholder="عنوان محصول" :readonly="isView" />
                </div>
                <div class="col-md-3">
                  <label class="form-label">حداقل سفارش *</label>
                  <input type="number" min="0" class="form-control" v-model="product.min_buy"
                    placeholder="شناسه اختصاصی (بارکد) محصول" :readonly="isView" />
                </div>
                <div class="col-md-3">
                  <label class="form-label">حداکثر سفارش *</label>
                  <input type="number" min="0" class="form-control" v-model="product.max_buy" placeholder="عنوان محصول"
                    :readonly="isView" />
                </div>
                <div class="col-md-3">
                  <label class="form-label">هشدار کاهش موجودی *</label>
                  <input type="number" min="0" class="form-control" v-model="product.alert"
                    placeholder="شناسه اختصاصی (بارکد) محصول" :readonly="isView" />
                </div>

                <!-- توضیحات -->
                <div class="col-12">
                  <label class="form-label">توضیحات</label>
                  <textarea rows="5" class="form-control" v-model="product.des" id="product-description"
                    placeholder="توضیحات محصول" :readonly="isView"></textarea>
                </div>

                <!-- تنوع‌های محصول -->
                <div class="mt-4">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">تنوع‌های محصول</h6>
                    <button type="button" class="btn btn-success btn-sm" @click="addVariant" v-if="!isView">
                      <i class="fa fa-plus me-1"></i>
                      افزودن تنوع
                    </button>
                  </div>

                  <div v-for="(variant, index) in productVariants" :key="index"
                    class="variant-card border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h6 class="mb-0">تنوع {{ index + 1 }}</h6>
                      <button type="button" class="btn btn-danger btn-sm" @click="removeVariant(index)"
                        v-if="!isView && productVariants.length > 1">
                        <i class="fa fa-trash me-1"></i>
                        حذف
                      </button>
                    </div>

                    <div class="row g-3">
                      <!-- قیمت و موجودی -->
                      <div class="col-md-3">
                        <label class="form-label">عنوان محصول *</label>
                        <input type="text" class="form-control" v-model="variant.name"
                          :placeholder="`مثال: سایز ${index + 1} یا رنگ ${index + 1}`" :readonly="isView" />
                      </div>

                      <div class="col-md-2">
                        <label class="form-label">قیمت اول دوره *</label>
                        <widgets.CurrencyInput v-model="variant.firstPrice" inputClass="form-control" :readonly="isView" />
                      </div>

                      <div class="col-md-2">
                        <label class="form-label">موجودی انبار *</label>
                        <input type="number" class="form-control" v-model="variant.firstWarehouse" min="0"
                          placeholder="0" :readonly="isView" :required="!isView" />
                      </div>

                      <div class="col-md-3">
                        <label class="form-label" for="convertUnit">تبدیل واحد </label>
                        <input type="checkbox" class="mx-2" id="convertUnit" v-model="variant.convertUnit"
                          :readonly="isView" value="1" @change="variant.UnitNumber = 0; variant.selectConvertUnit = 0" />
                        <span v-if="variant.convertUnit && variant.convert_unit_relation">({{
                          variant.convert_unit_relation.title
                        }})</span>
                        <div class="d-flex">
                          <div>
                            <input type="number" class="form-control" v-model="variant.UnitNumber" min="0"
                              placeholder="0" :readonly="isView || !variant.convertUnit" />
                          </div>
                          <div>
                            <widgets.searchinput placeholder="واحد اندازه گیری" v-model="variant.selectConvertUnit"
                              textSearchUrl="/products/units/list" idSearchUrl="/products/units/" methode="GET"
                              :columns="[{ label: 'عنوان', key: 'title' }]"
                              :disabled="isView || !variant.convertUnit" />

                          </div>
                        </div>

                      </div>


                    </div>
                  </div>
                </div>


              </div>
            </div>
          </div>
        </div>

        <!-- سایدبار تصاویر -->
        <div class="col-md-3">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">تصاویر محصول</h5>
            </div>
            <div class="card-body">
              <button type="button" class="btn btn-info w-100 mb-3" @click="openImageManager" v-if="!isView">
                <i class="fa fa-image me-1"></i>
                انتخاب تصویر
              </button>

              <div class="product-images" dir="rtl">
                <div v-for="(img, index) in productImages" :key="`${index}-${img.url}`"
                  class="image-item mb-2 position-relative" :class="{ 'removed-image': img.removed }">
                  <img :src="img.thumb || img.url" class="img-thumbnail w-100" style="height: 150px; object-fit: cover;"
                    :alt="`تصویر ${index + 1}`" />
                  <button type="button" class="btn btn-danger btn-sm position-absolute top-0 start-0 m-1"
                    @click="removeImage(index)" title="حذف تصویر" v-if="!isView && !img.removed">
                    <i class="fa fa-times"></i>
                  </button>
                  <button type="button" class="btn btn-warning btn-sm position-absolute top-0 start-0 m-1"
                    @click="restoreImage(index)" title="بازیابی تصویر" v-if="!isView && img.removed">
                    <i class="fa fa-undo"></i>
                  </button>
                  <div
                    class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-50 text-white text-center p-1">
                    تصویر {{ index + 1 }}
                    <span v-if="img.removed" class="badge bg-danger ms-1">حذف شده</span>
                  </div>
                </div>
                <div v-if="productImages.filter(img => !img.removed).length === 0" class="text-center text-muted py-5">
                  <i class="fa fa-image fa-3x mb-2"></i>
                  <div>هیچ تصویری انتخاب نشده</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { watch, nextTick } from 'vue'
import Swal from 'sweetalert2'
import CurrencyInput from '@/components/widgets/CurrencyInput.vue'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth',
  title: 'مدیریت محصول'
})

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const { $api } = useNuxtApp()

// تشخیص حالت صفحه: 'add', 'view', 'edit'
const pageMode = computed(() => {
  const id = route.params.id
  if (!id || id === 'add') return 'add'
  if (typeof id === 'string' && id.startsWith('edit_')) return 'edit'
  return 'view'
})

// استخراج ID محصول از route
const productId = computed(() => {
  const id = route.params.id
  if (pageMode.value === 'edit') {
    return id.replace('edit_', '')
  }
  if (pageMode.value === 'view') {
    return id
  }
  return null
})

const isAdd = computed(() => pageMode.value === 'add')
const isEdit = computed(() => pageMode.value === 'edit')
const isView = computed(() => pageMode.value === 'view')

const formloading = ref(false)
const formError = ref(null)

const product = ref({
  id: '',
  title: '',
  barcode: '',
  album: [],
  tags: '',
  form: '',
  tax_rate: 0,
  min_buy: 0,
  max_buy: 0,
  alert: '',
  status: 0,
  product_items: []
})
const productImages = ref([])

// تنوع‌های محصول
const productVariants = ref([
  {
    id: '',
    title: '',
    f_id: '',
    firstWarehouse: 0,
    current_stock: 0,
    firstPrice: 0,
    sell_price: 0,
    status: 0,
  }
])

const selectedUnit = ref(null)
const selectedBrand = ref(null)
const selectedWarehouse = ref(null)
// متغیرهای دسته‌بندی‌های آبشاری
const selectedMainCategory = ref(null)
const selectedSubCategory = ref(null)
const selectedSubSubCategory = ref(null)
const isLoadingCategories = ref(false)



watch(selectedMainCategory, (newCategory, oldCategory) => {
  if (!newCategory || isLoadingCategories.value) return
  selectedSubCategory.value = null
  selectedSubSubCategory.value = null
})
watch(selectedSubCategory, (newCategory, oldCategory) => {
  if (!newCategory || isLoadingCategories.value) return
  selectedSubSubCategory.value = null
})
// watch(selectedSubSubCategory, (newCategory, oldCategory) => {
//   if (!newCategory) return

// })



// توابع مدیریت تنوع‌های محصول
const addVariant = () => {
  productVariants.value.push({
    id: '',
    name: '',
    firstWarehouse: 0,
    firstPrice: 0,
    current_stock: 0,
    sell_price: 0,
    status: 1,
    convertUnit: false,
    UnitNumber: 0,
    selectConvertUnit: null,
    convert_unit_relation: null
  })
}

const removeVariant = (index) => {
  if (productVariants.value.length > 1) {
    productVariants.value.splice(index, 1)
  }
}


// رفتن به حالت ویرایش
const goToEdit = () => {
  router.push(`/dashboard/products/edit_${productId.value}`)
}


// بارگذاری محصول برای ویرایش
const loadProduct = async () => {
  formloading.value = true
  try {
    // تغییر از GET به POST
    const response = await $api(`/products/${productId.value}`, {
      method: 'POST',
      body: {
      }
    })
    const data = response.data
    console.log(response)

    product.value = {
      title: data.title || '',
      barcode: data.barcode || '',
      des: data.des || '',
      status: data.status ?? 1,
      form: data.form || null,
      tax_rate: data.tax_rate || 10,
      min_buy: data.min_buy || 0,
      max_buy: data.max_buy || 0,
      alert: data.alert || 0
    }

    // بارگذاری تنوع‌ها (product_items)
    if (data.variants && Array.isArray(data.variants) && data.variants.length > 0) {
      productVariants.value = data.variants.map(variant => ({
        id: variant.id,
        name: variant.title || '',
        firstWarehouse: variant.firstWarehouse || 0,
        firstPrice: parseFloat(variant.firstPrice) || 0,
        current_stock: variant.current_stock || 0,
        sell_price: parseFloat(variant.sell_price) || 0,
        status: variant.status ?? 1,
        // بارگذاری فیلدهای تبدیل واحد از دیتابیس
        convertUnit: variant.convertUnit || false,
        UnitNumber: variant.UnitNumber || 0,
        selectConvertUnit: variant.selectConvertUnit || null,
        convert_unit_relation: variant.convert_unit_relation
      }))
    } else {
      // اگر تنوع وجود ندارد، یک تنوع خالی ایجاد کن
      productVariants.value = [{
        id: '',
        name: '',
        firstWarehouse: 0,
        firstPrice: 0,
        current_stock: 0,
        sell_price: 0,
        status: 1,
        convertUnit: false,
        UnitNumber: 0,
        selectConvertUnit: null,
        convert_unit_relation: null
      }]
    }

    // بارگذاری تصاویر
    if (data.album) {
      const album = typeof data.album === 'string' ? JSON.parse(data.album) : data.album

      if (Array.isArray(album)) {
        productImages.value = album.map(img => ({
          thumb: img.thumb || img.url,
          url: img.url,
          removed: false
        }))
      }
    }

    // بارگذاری دسته‌بندی‌های انتخاب شده
    if (data.categores && Array.isArray(data.categores) && data.categores.length > 0) {
      // جلوگیری از پاک شدن زیردسته‌ها توسط watcher ها
      isLoadingCategories.value = true

      const categories = data.categores

      // مرتب‌سازی بر اساس سطح (f_id = 0 یعنی دسته اصلی)
      categories.sort((a, b) => (a.f_id || 0) - (b.f_id || 0))

      // تنظیم دسته‌بندی اصلی (f_id = 0 یا null یعنی بدون والد)
      const mainCategory = categories.find(cat => !cat.f_id) || categories[0]
      if (mainCategory) {
        selectedMainCategory.value = mainCategory
      }

      // تنظیم دسته‌بندی فرزند
      const subCategory = categories.find(cat => cat.f_id === mainCategory?.id)
      if (subCategory) {
        selectedSubCategory.value = subCategory

        // تنظیم دسته‌بندی فرزند دوم
        const subSubCategory = categories.find(cat => cat.f_id === subCategory.id)
        if (subSubCategory) {
          selectedSubSubCategory.value = subSubCategory
        }
      }

      // بعد از ست شدن همه مقادیر، flag رو غیرفعال کن
      nextTick(() => {
        isLoadingCategories.value = false
      })
    }

    // تنظیم واحد، برند و انبار از روابط مستقیم
    if (data.unit) {
      selectedUnit.value = data.unit
    }

    if (data.brand) {
      selectedBrand.value = data.brand
    }

    if (data.warehouse) {
      selectedWarehouse.value = data.warehouse
    }

  } catch (err) {
    formError.value = 'خطا در بارگذاری محصول'
    console.error('Error loading product:', err)
  } finally {
    formloading.value = false
  }
}

// مدیریت تصاویر
const openImageManager = () => {
  // این بخش باید با سیستم مدیریت فایل شما یکپارچه شود
  // برای نمونه، یک input file ساده
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/*'
  input.multiple = true
  input.onchange = (e) => {
    const files = Array.from(e.target.files)
    files.forEach(file => {
      const reader = new FileReader()
      reader.onload = (event) => {
        productImages.value.push({
          thumb: event.target.result,
          url: event.target.result,
          file: file
        })
      }
      reader.readAsDataURL(file)
    })
  }
  input.click()
}

const removeImage = (index) => {
  const img = productImages.value[index]
  if (img.file) {
    // New image that was uploaded but now removed - remove entirely
    productImages.value.splice(index, 1)
  } else {
    // Existing image - mark as removed
    img.removed = true
  }
}

const restoreImage = (index) => {
  productImages.value[index].removed = false
}

// Helper function to append product fields to FormData
const appendProductFields = (formData, product, editorContent) => {
  // console.log(selectedMainCategory.value)

  const fieldMap = {
    status: product.status ?? 1,
    title: product.title,
    barcode: product.barcode,
    tax_rate: product.tax_rate || 10,
    min_buy: product.min_buy || 0,
    max_buy: product.max_buy || 0,
    alert: product.alert || 0,
    des: editorContent || product.des,

    selectedUnit: selectedUnit.value && selectedUnit.value.id ? selectedUnit.value.id : 0,
    selectedBrand: selectedBrand.value && selectedBrand.value.id ? selectedBrand.value.id : 0,
    selectedWarehouse: selectedWarehouse.value && selectedWarehouse.value.id ? selectedWarehouse.value.id : 0,

  }

  // ارسال دسته‌بندی‌ها به صورت آرایه
  if (selectedMainCategory.value && selectedMainCategory.value.id) {
    formData.append('Categores[]', selectedMainCategory.value.id)
  }
  if (selectedSubCategory.value && selectedSubCategory.value.id) {
    formData.append('Categores[]', selectedSubCategory.value.id)
  }
  if (selectedSubSubCategory.value && selectedSubSubCategory.value.id) {
    formData.append('Categores[]', selectedSubSubCategory.value.id)
  }

  // ارسال تنوع‌ها به صورت آرایه
  productVariants.value.forEach((variant, index) => {
    const variantName = variant.name?.trim() || product.title?.trim() || ''
    // ارسال id برای تنوع‌های موجود (جهت آپدیت بجای حذف و ساخت مجدد)
    if (variant.id) {
      formData.append(`variants[${index}][id]`, variant.id)
    }
    formData.append(`variants[${index}][title]`, variantName)
    formData.append(`variants[${index}][firstWarehouse]`, variant.firstWarehouse || 0)
    formData.append(`variants[${index}][firstPrice]`, variant.firstPrice || 0)
    formData.append(`variants[${index}][current_stock]`, variant.current_stock || 0)
    formData.append(`variants[${index}][sell_price]`, variant.sell_price || 0)
    formData.append(`variants[${index}][status]`, variant.status ?? 1)
    formData.append(`variants[${index}][convertUnit]`, variant.convertUnit ? 1 : 0)
    formData.append(`variants[${index}][UnitNumber]`, variant.UnitNumber || 0)
    if (variant.selectConvertUnit && variant.selectConvertUnit.id) {
      formData.append(`variants[${index}][selectConvertUnit]`, variant.selectConvertUnit.id)
    } else if (variant.selectConvertUnit) {
      formData.append(`variants[${index}][selectConvertUnit]`, variant.selectConvertUnit)
    }
  })

  Object.entries(fieldMap).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      formData.append(key, value)
    }
  })

  // Add form if exists
  if (product.form) {
    formData.append('form', JSON.stringify(product.form))
  }
}

// ساخت payload آلبوم برای ارسال به سرور
const buildAlbumPayload = (images) => {
  if (!images.length) return null

  const album = {
    existing: [],
    removed: []
  }

  images.forEach(img => {
    if (img.removed) {
      album.removed.push({
        url: img.url,
        filename: img.url ? img.url.split('/').pop() : ''
      })
    } else if (!img.file) {
      album.existing.push({
        url: img.url,
        thumb: img.thumb
      })
    }
  })

  return album
}

// ذخیره محصول
const saveProduct = async (action) => {
  if (!product.value.title?.trim()) {
    formError.value = 'عنوان محصول الزامی است'
    return
  }

  formloading.value = true
  formError.value = null

  try {
    const formData = new FormData()

    // Get editor content
    let editorContent = null
    if (process.client && window.tinymce) {
      editorContent = window.tinymce.get('product-description')?.getContent()
    }

    // Append product fields
    appendProductFields(formData, product.value, editorContent)

    // Build and append album payload
    const album = buildAlbumPayload(productImages.value)
    if (album) {
      formData.append('album', JSON.stringify(album))

      // Append new image files
      productImages.value.forEach((img, index) => {
        if (img.file && !img.removed) {
          formData.append(`images[${index}]`, img.file)
        }
      })
    }

    // For updates, use _method=PUT with POST
    if (isEdit.value) {
      formData.append('_method', 'PUT')
    }


    const requestConfig = {
      method: 'POST',
      body: formData
    }


    const url = isEdit.value ? `/products/${productId.value}` : '/products'
    const response = await $api(url, requestConfig)
    console.log(response)

    // نمایش پیام موفقیت
    await Swal.fire({
      title: 'انجام شد!',
      text: 'محصول با موفقیت ذخیره شد.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
      customClass: {
        popup: 'swal-rtl'
      }
    })

    // انجام عملیات بعد از ذخیره
    if (action === 'new') {
      router.push('/dashboard/products/add')
    } else if (action === 'exit') {
      router.push('/dashboard/products')
    } else if (isAdd.value && response.data?.id) {
      router.push(`/dashboard/products/edit_${response.data.id}`)
    } else if (isEdit.value) {
      await loadProduct()
    }

  } catch (err) {
    const status = err?.response?.status
    const data = err?.response?._data

    if (status === 422 && data?.errors) {
      formError.value = Object.values(data.errors).flat().join(' ، ')
    } else if (data?.message) {
      formError.value = data.message
    } else {
      formError.value = 'خطایی در ارتباط با سرور رخ داد'
    }
  } finally {
    formloading.value = false
  }
}

const saveAndNew = () => {
  saveProduct('new')
}

const saveAndExit = () => {
  saveProduct('exit')
}

// حذف محصول
const deleteProduct = async () => {
  const result = await Swal.fire({
    title: 'تأیید حذف',
    text: 'آیا مطمئن هستید که می‌خواهید این محصول را حذف کنید؟',
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

  if (result.isConfirmed) {
    formloading.value = true
    try {
      await $api(`/products/${productId.value}`, {
        method: 'DELETE'
      })

      await Swal.fire({
        title: 'انجام شد!',
        text: 'محصول با موفقیت حذف شد.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        customClass: {
          popup: 'swal-rtl'
        }
      })

      router.push('/dashboard/products')
    } catch (err) {
      formError.value = 'خطا در حذف محصول'
    } finally {
      formloading.value = false
    }
  }
}

// بازیابی محصول (اگر backend از soft delete استفاده می‌کند، این endpoint باید در backend اضافه شود)
const restoreProduct = async () => {
  const result = await Swal.fire({
    title: 'تأیید بازیابی',
    text: 'آیا می‌خواهید این محصول را بازیابی کنید؟',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'بله، بازیابی کن',
    cancelButtonText: 'لغو',
    reverseButtons: true,
    customClass: {
      popup: 'swal-rtl'
    }
  })

  if (result.isConfirmed) {
    formloading.value = true
    try {
      await $api(`/products/${productId.value}/restore`, {
        method: 'PATCH'
      })

      await Swal.fire({
        title: 'انجام شد!',
        text: 'محصول با موفقیت بازیابی شد.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        customClass: {
          popup: 'swal-rtl'
        }
      })

      await loadProduct()
    } catch (err) {
      formError.value = 'خطا در بازیابی محصول'
    } finally {
      formloading.value = false
    }
  }
}

// حذف کامل محصول (اگر backend از soft delete استفاده می‌کند، این endpoint باید در backend اضافه شود)
const forceDeleteProduct = async () => {
  const result = await Swal.fire({
    title: 'تأیید حذف کامل',
    text: 'آیا مطمئن هستید که می‌خواهید این محصول را برای همیشه حذف کنید؟ این عمل قابل برگشت نیست!',
    icon: 'error',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'بله، برای همیشه حذف کن',
    cancelButtonText: 'لغو',
    reverseButtons: true,
    customClass: {
      popup: 'swal-rtl'
    }
  })

  if (result.isConfirmed) {
    formloading.value = true
    try {
      await $api(`/products/${productId.value}/force`, {
        method: 'DELETE'
      })

      await Swal.fire({
        title: 'انجام شد!',
        text: 'محصول برای همیشه حذف شد.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        customClass: {
          popup: 'swal-rtl'
        }
      })

      router.push('/dashboard/products')
    } catch (err) {
      formError.value = 'خطا در حذف کامل محصول'
    } finally {
      formloading.value = false
    }
  }
}

// بارگذاری محصول در حالت edit/view
watch(productId, async (newId, oldId) => {
  if (newId && (isEdit.value || isView.value)) {
    await loadProduct()
  }
}, { immediate: true })

// کلید میانبر Ctrl+S (فقط برای حالت add/edit)
onMounted(() => {
  if (process.client && !isView.value) {
    const handleKeyDown = (e) => {
      if (e.ctrlKey && e.key === 's') {
        e.preventDefault()
        saveProduct()
      }
    }
    document.addEventListener('keydown', handleKeyDown)
    onUnmounted(() => {
      document.removeEventListener('keydown', handleKeyDown)
    })
  }
})
</script>

<style scoped>
.product-add-page {
  padding: 20px 0;
}

.product-images {
  max-height: 600px;
  overflow-y: auto;
}

.image-item {
  position: relative;
}

.image-item img {
  cursor: pointer;
  transition: opacity 0.3s;
}

.image-item:hover img {
  opacity: 0.8;
}

.swal-rtl {
  direction: rtl;
}

.swal-rtl .swal2-popup .swal2-actions {
  flex-direction: row-reverse;
}

/* استایل برای select multiple */
select[multiple] {
  min-height: 120px;
}

/* استایل برای تصاویر حذف شده */
.removed-image {
  opacity: 0.5;
  filter: grayscale(100%);
}

.removed-image img {
  border: 2px solid #dc3545;
}

/* استایل برای کارت‌های تنوع */
.variant-card {
  background-color: #f8f9fa;
  border-color: #dee2e6;
}

.variant-card h6 {
  color: #495057;
}
</style>
