<template>
  <div class="customer-form-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">ویرایش مشتری</h4>
      <NuxtLink to="/accounting/customers" class="btn btn-outline-secondary">
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
                  <label for="name" class="form-label">نام مشتری <span class="text-danger">*</span></label>
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
                  <label for="mobile" class="form-label">موبایل <span class="text-danger">*</span></label>
                  <input
                    id="mobile"
                    v-model="form.mobile"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.mobile }"
                    placeholder="09xxxxxxxxx"
                    required
                  >
                  <div v-if="errors.mobile" class="invalid-feedback">{{ errors.mobile }}</div>
                </div>

                <div class="col-md-6">
                  <label for="balance" class="form-label">مانده حساب (تومان)</label>
                  <input
                    id="balance"
                    v-model.number="form.balance"
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': errors.balance }"
                    step="0.01"
                  >
                  <div v-if="errors.balance" class="invalid-feedback">{{ errors.balance }}</div>
                  <small class="form-text text-muted">
                    مقدار مثبت = مشتری بدهکار است، مقدار منفی = مشتری بستانکار است
                  </small>
                </div>
              </div>

              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  ویرایش مشتری
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
  title: 'ویرایش مشتری'
})

const { $api } = useNuxtApp()
const route = useRoute()

const loading = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  mobile: '',
  balance: 0
})

const resetForm = () => {
  const customerId = route.params.id
  if (customerId) {
    loadCustomer(customerId)
  }
}

const validateForm = () => {
  errors.value = {}

  if (!form.name.trim()) {
    errors.value.name = 'نام مشتری الزامی است'
  }

  if (!form.mobile.trim()) {
    errors.value.mobile = 'موبایل الزامی است'
  } else if (!/^09\d{9}$/.test(form.mobile)) {
    errors.value.mobile = 'فرمت موبایل صحیح نیست'
  }

  return Object.keys(errors.value).length === 0
}

const submitForm = async () => {
  if (!validateForm()) return

  loading.value = true
  try {
    const customerId = route.params.id
    await $api.put(`/customers/${customerId}`, form)
    alert('مشتری با موفقیت ویرایش شد')
    navigateTo('/accounting/customers')
  } catch (error) {
    console.error('Error updating customer:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('خطا در ویرایش مشتری')
    }
  } finally {
    loading.value = false
  }
}

const loadCustomer = async (id) => {
  loading.value = true
  try {
    const response = await $api.get(`/customers/${id}`)
    Object.assign(form, response.data)
  } catch (error) {
    console.error('Error loading customer:', error)
    alert('خطا در بارگذاری مشتری')
    navigateTo('/accounting/customers')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  const customerId = route.params.id
  if (customerId) {
    loadCustomer(customerId)
  } else {
    navigateTo('/accounting/customers')
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