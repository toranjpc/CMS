<template>
  <div class="product-form-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">ویرایش محصول</h4>
      <NuxtLink to="/accounting/products" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-right me-1"></i>بازگشت به لیست
      </NuxtLink>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <div v-if="loading" class="text-center py-4">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">در حال بارگذاری...</span>
              </div>
            </div>

            <form v-else @submit.prevent="submitForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="name" class="form-label">نام محصول <span class="text-danger">*</span></label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.name }"
                    required
                  >
                  <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                </div>

                <div class="col-md-6">
                  <label for="code" class="form-label">کد محصول <span class="text-danger">*</span></label>
                  <input
                    id="code"
                    v-model="form.code"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.code }"
                    required
                  >
                  <div v-if="errors.code" class="invalid-feedback">{{ errors.code }}</div>
                </div>

                <div class="col-md-6">
                  <label for="buy_price" class="form-label">قیمت خرید <span class="text-danger">*</span></label>
                  <input
                    id="buy_price"
                    v-model.number="form.buy_price"
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': errors.buy_price }"
                    min="0"
                    step="0.01"
                    required
                  >
                  <div v-if="errors.buy_price" class="invalid-feedback">{{ errors.buy_price }}</div>
                </div>

                <div class="col-md-6">
                  <label for="sell_price" class="form-label">قیمت فروش <span class="text-danger">*</span></label>
                  <input
                    id="sell_price"
                    v-model.number="form.sell_price"
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': errors.sell_price }"
                    min="0"
                    step="0.01"
                    required
                  >
                  <div v-if="errors.sell_price" class="invalid-feedback">{{ errors.sell_price }}</div>
                </div>

                <div class="col-md-6">
                  <label for="stock" class="form-label">موجودی <span class="text-danger">*</span></label>
                  <input
                    id="stock"
                    v-model.number="form.stock"
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': errors.stock }"
                    min="0"
                    required
                  >
                  <div v-if="errors.stock" class="invalid-feedback">{{ errors.stock }}</div>
                </div>

                <div class="col-md-6">
                  <label for="min_stock" class="form-label">حداقل موجودی <span class="text-danger">*</span></label>
                  <input
                    id="min_stock"
                    v-model.number="form.min_stock"
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': errors.min_stock }"
                    min="0"
                    required
                  >
                  <div v-if="errors.min_stock" class="invalid-feedback">{{ errors.min_stock }}</div>
                </div>
              </div>

              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  ویرایش محصول
                </button>
                <button type="button" @click="resetForm" class="btn btn-outline-secondary">
                  بازنشانی
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, navigateTo } from '#app'

definePageMeta({
  layout: 'accounting',
  title: 'ویرایش محصول'
})

const { $api } = useNuxtApp()
const route = useRoute()

const loading = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  code: '',
  buy_price: 0,
  sell_price: 0,
  stock: 0,
  min_stock: 0
})

const resetForm = () => {
  const productId = route.params.id
  if (productId) {
    loadProduct(productId)
  }
}

const validateForm = () => {
  errors.value = {}

  if (!form.name.trim()) {
    errors.value.name = 'نام محصول الزامی است'
  }

  if (!form.code.trim()) {
    errors.value.code = 'کد محصول الزامی است'
  }

  if (form.buy_price <= 0) {
    errors.value.buy_price = 'قیمت خرید باید بزرگتر از صفر باشد'
  }

  if (form.sell_price <= 0) {
    errors.value.sell_price = 'قیمت فروش باید بزرگتر از صفر باشد'
  }

  if (form.stock < 0) {
    errors.value.stock = 'موجودی نمی‌تواند منفی باشد'
  }

  if (form.min_stock < 0) {
    errors.value.min_stock = 'حداقل موجودی نمی‌تواند منفی باشد'
  }

  return Object.keys(errors.value).length === 0
}

const submitForm = async () => {
  if (!validateForm()) return

  loading.value = true
  try {
    const productId = route.params.id
    await $api.put(`/products/${productId}`, form)
    alert('محصول با موفقیت ویرایش شد')
    navigateTo('/accounting/products')
  } catch (error) {
    console.error('Error updating product:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('خطا در ویرایش محصول')
    }
  } finally {
    loading.value = false
  }
}

const loadProduct = async (id) => {
  loading.value = true
  try {
    const response = await $api.get(`/products/${id}`)
    Object.assign(form, response.data)
  } catch (error) {
    console.error('Error loading product:', error)
    alert('خطا در بارگذاری محصول')
    navigateTo('/accounting/products')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  const productId = route.params.id
  if (productId) {
    loadProduct(productId)
  } else {
    navigateTo('/accounting/products')
  }
})
</script>

<style scoped>
.form-label {
  font-weight: 500;
}

.card {
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn {
  border-radius: 6px;
  padding: 0.5rem 1.5rem;
}
</style>