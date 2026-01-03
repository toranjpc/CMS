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

    <form @submit.prevent="saveProduct('add')" enctype="multipart/form-data" id="productForm">
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

              <div class="row g-3">
                <!-- عنوان -->
                <div class="col-md-6">
                  <label class="form-label">عنوان *</label>
                  <input type="text" class="form-control" v-model="product.title" placeholder="عنوان محصول"
                    :readonly="isView" :required="!isView" />
                </div>

                <!-- دسته بندی -->
                <div class="col-md-6">
                  <label class="form-label">دسته بندی</label>
                  <select class="form-select" v-model="selectedCategories" multiple :disabled="isView">
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                      {{ cat.title }}
                    </option>
                  </select>
                  <small class="text-muted" v-if="!isView">برای انتخاب چندتایی، Ctrl را نگه دارید</small>
                  <div v-else-if="selectedCategories.length > 0" class="mt-2">
                    <span v-for="(catId, index) in selectedCategories" :key="catId" class="badge bg-secondary me-1">
                      {{ getCategoryName(catId) }}
                    </span>
                  </div>
                </div>

                <!-- کلمات کلیدی -->
                <div class="col-12">
                  <label class="form-label">کلمات کلیدی (سئو)</label>
                  <input type="text" class="form-control" v-model="product.tags"
                    placeholder="کلمات کلیدی را با کاما جدا کنید" :readonly="isView" />
                </div>

                <!-- توضیحات کوتاه -->
                <div class="col-12">
                  <label class="form-label">توضیحات کوتاه</label>
                  <textarea rows="3" class="form-control" v-model="product.minDes" placeholder="توضیحات کوتاه محصول"
                    :readonly="isView"></textarea>
                </div>

                <!-- توضیحات کامل -->
                <div class="col-12">
                  <label class="form-label">توضیحات کامل</label>
                  <textarea rows="15" class="form-control" v-model="product.des" id="product-description"
                    placeholder="توضیحات کامل محصول" :readonly="isView"></textarea>
                </div>

                <!-- قیمت و موجودی -->
                <div class="col-md-3">
                  <label class="form-label">قیمت محصول *</label>
                  <input type="number" class="form-control" v-model.number="product.firstPrice" step="0.01" min="0"
                    placeholder="0" :readonly="isView" :required="!isView" />
                </div>

                <div class="col-md-3">
                  <label class="form-label">قیمت بدون تخفیف</label>
                  <input type="number" class="form-control" v-model.number="product.sell_price" step="0.01" min="0"
                    placeholder="0" :readonly="isView" />
                </div>

                <div class="col-md-3">
                  <label class="form-label">موجودی انبار *</label>
                  <input type="number" class="form-control" v-model.number="product.firstWarehouse" min="0"
                    placeholder="0" :readonly="isView" :required="!isView" />
                </div>

                <div class="col-md-3">
                  <label class="form-label">نرخ مالیات (%)</label>
                  <input type="number" class="form-control" v-model.number="product.tax_rate" min="0" max="100"
                    placeholder="10" :readonly="isView" />
                </div>

                <!-- حداقل و حداکثر خرید -->
                <div class="col-md-3">
                  <label class="form-label">حداقل سفارش *</label>
                  <input type="number" class="form-control" v-model.number="product.min_buy" min="0" placeholder="0"
                    :readonly="isView" :required="!isView" />
                </div>

                <div class="col-md-3">
                  <label class="form-label">حداکثر سفارش *</label>
                  <input type="number" class="form-control" v-model.number="product.max_buy" min="0" placeholder="0"
                    :readonly="isView" :required="!isView" />
                </div>

                <div class="col-md-3">
                  <label class="form-label">هشدار کاهش موجودی *</label>
                  <input type="number" class="form-control" v-model.number="product.alert" min="0" placeholder="0"
                    :readonly="isView" :required="!isView" />
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
                  class="image-item mb-2 position-relative"
                  :class="{ 'removed-image': img.removed }">
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
import Swal from 'sweetalert2'

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
  title: '',
  tags: '',
  des: '',
  minDes: '',
  firstWarehouse: 0,
  firstPrice: 0,
  sell_price: 0,
  tax_rate: 10,
  min_buy: 0,
  max_buy: 0,
  alert: 0,
  status: 1,
  form: null
})

const categories = ref([])
const selectedCategories = ref([])
const productImages = ref([])

// بارگذاری داده‌های اولیه
onMounted(async () => {
  await loadCategories()

  if (isEdit.value || isView.value) {
    await loadProduct()
  } else {
    // مقداردهی اولیه برای محصول جدید
    product.value.status = 1
    product.value.tax_rate = 10
  }

  // راه‌اندازی ویرایشگر متن (فقط برای حالت add/edit)
  if (process.client && !isView.value) {
    await initEditor()
  }
})

// تابع برای گرفتن نام دسته‌بندی
const getCategoryName = (catId) => {
  const cat = categories.value.find(c => c.id == catId)
  return cat ? cat.title : `دسته ${catId}`
}

// رفتن به حالت ویرایش
const goToEdit = () => {
  router.push(`/dashboard/products/edit_${productId.value}`)
}

// بارگذاری دسته‌بندی‌ها
const loadCategories = async () => {
  try {
    const response = await $api('/products/categories?all=1')
    categories.value = response.data?.items || response.data?.Data || []
  } catch (err) {
    console.error('Error loading categories:', err)
  }
}

// بارگذاری محصول برای ویرایش
const loadProduct = async () => {
  formloading.value = true
  try {
    const response = await $api(`/products/${productId.value}`)
    const data = response.data

    product.value = {
      title: data.title || '',
      tags: data.tags || '',
      des: data.des || '',
      minDes: data.minDes || '',
      firstWarehouse: data.firstWarehouse || 0,
      firstPrice: parseFloat(data.firstPrice) || 0,
      sell_price: parseFloat(data.sell_price) || 0,
      tax_rate: data.tax_rate || 10,
      min_buy: data.min_buy || 0,
      max_buy: data.max_buy || 0,
      alert: data.alert || 0,
      status: data.status ?? 1,
      form: data.form || null
    }

    // بارگذاری تصاویر
    if (data.album) {
      const album = typeof data.album === 'string' ? JSON.parse(data.album) : data.album
      if (album.thumbs && Array.isArray(album.thumbs)) {
        productImages.value = album.thumbs.map((thumb, index) => ({
          thumb: thumb,
          url: album.base && album.base[index] ? album.base[index] : thumb,
          removed: false
        }))
      } else if (album.thumbs) {
        productImages.value = [{
          thumb: album.thumbs,
          url: album.base || album.thumbs,
          removed: false
        }]
      }
    }

    // بارگذاری دسته‌بندی‌های انتخاب شده
    if (data.categories && Array.isArray(data.categories)) {
      selectedCategories.value = data.categories.map(cat => cat.id)
    } else if (data.category_id) {
      selectedCategories.value = [data.category_id]
    }

    // راه‌اندازی مجدد ویرایشگر
    await nextTick()
    if (process.client) {
      await initEditor()
    }
  } catch (err) {
    formError.value = 'خطا در بارگذاری محصول'
    console.error('Error loading product:', err)
  } finally {
    formloading.value = false
  }
}

// راه‌اندازی ویرایشگر متن (TinyMCE یا ویرایشگر ساده)
const initEditor = async () => {
  // اگر TinyMCE موجود است، از آن استفاده کنید
  if (typeof window !== 'undefined' && window.tinymce) {
    // حذف ویرایشگر قبلی اگر وجود دارد
    const existingEditor = window.tinymce.get('product-description')
    if (existingEditor) {
      existingEditor.remove()
    }

    await nextTick()

    window.tinymce.init({
      selector: '#product-description',
      language: 'fa',
      directionality: 'rtl',
      height: 400,
      readonly: isView.value,
      plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
      toolbar: isView.value ? '' : 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
      content_style: 'body { font-family: Tahoma, Arial; font-size: 14px; }'
    })
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
  const fieldMap = {
    title: product.title,
    tags: product.tags,
    des: editorContent || product.des,
    minDes: product.minDes,
    firstWarehouse: product.firstWarehouse || 0,
    firstPrice: product.firstPrice || 0,
    sell_price: product.sell_price || 0,
    tax_rate: product.tax_rate || 10,
    min_buy: product.min_buy || 0,
    max_buy: product.max_buy || 0,
    alert: product.alert || 0,
    status: product.status ?? 1
  }

  Object.entries(fieldMap).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      formData.append(key, value)
    }
  })

  // Add categories
  selectedCategories.value.forEach(catId => {
    formData.append('categories[]', catId)
  })

  // Add form if exists
  if (product.form) {
    formData.append('form', JSON.stringify(product.form))
  }
}

// Helper function to build album payload
const buildAlbumPayload = (images) => {
  if (!images.length) return null

  const album = {
    existing: [],
    new: [],
    removed: []
  }

  images.forEach((img, index) => {
    if (img.removed) {
      album.removed.push({ index, id: img.id || img.url })
    } else if (img.file) {
      // New file - will be sent separately
      album.new.push({ index, filename: img.file.name })
    } else {
      // Existing image - keep reference
      album.existing.push({
        index,
        url: img.url,
        thumb: img.thumb
      })
    }
  })

  return album
}

// ذخیره محصول
const saveProduct = async (action = 'update') => {
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

    const requestConfig = {
      method: 'POST',
      body: formData
    }

    // For updates, use _method=PUT with POST
    if (isEdit.value) {
      formData.append('_method', 'PUT')
    }

    const url = isEdit.value ? `/products/${productId.value}` : '/products'
    const response = await $api(url, requestConfig)

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
      // اگر endpoint restore در backend وجود دارد، از آن استفاده کنید
      // در غیر این صورت، می‌توانید status را تغییر دهید
      await $api(`/products/${productId.value}`, {
        method: 'PUT',
        body: { status: 1 }
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
      // اگر endpoint force delete در backend وجود دارد، از آن استفاده کنید
      // در غیر این صورت، از همان DELETE استفاده می‌شود
      await $api(`/products/${productId.value}`, {
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
</style>
