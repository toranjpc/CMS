<template>
  <div class="customer-form-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">افزودن مشتری جدید</h4>
      <NuxtLink to="/accounting/customers" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-right me-1"></i>بازگشت به لیست
      </NuxtLink>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="submitForm">
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
                  افزودن مشتری
                </button>
                <button type="button" @click="resetForm" class="btn btn-outline-secondary">
                  پاک کردن فرم
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
import { ref, reactive } from 'vue'
import { navigateTo } from '#app'

definePageMeta({
  layout: 'accounting',
  title: 'افزودن مشتری'
})

const { $api } = useNuxtApp()

const loading = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  mobile: '',
  balance: 0
})

const resetForm = () => {
  Object.assign(form, {
    name: '',
    mobile: '',
    balance: 0
  })
  errors.value = {}
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
    await $api.post('/customers', form)
    alert('مشتری با موفقیت اضافه شد')
    resetForm()
  } catch (error) {
    console.error('Error creating customer:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('خطا در ذخیره مشتری')
    }
  } finally {
    loading.value = false
  }
}
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