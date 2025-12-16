<template>
  <div class="register-page">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card shadow-lg border-0">
            <div class="card-body p-5">
              <div class="text-center mb-4">
                <h2 class="fw-bold">ثبت نام</h2>
                <p class="text-muted">حساب کاربری جدید ایجاد کنید</p>
              </div>

              <form @submit.prevent="handleRegister">
                <!-- Name Field -->
                <div class="mb-3">
                  <label for="name" class="form-label">نام و نام خانوادگی</label>
                  <input
                    type="text"
                    class="form-control form-control-lg"
                    id="name"
                    v-model="form.name"
                    placeholder="نام و نام خانوادگی خود را وارد کنید"
                    required
                  />
                </div>

                <!-- Email Field -->
                <div class="mb-3">
                  <label for="email" class="form-label">ایمیل</label>
                  <input
                    type="email"
                    class="form-control form-control-lg"
                    id="email"
                    v-model="form.email"
                    placeholder="ایمیل خود را وارد کنید"
                    required
                  />
                </div>

                <!-- Password Field -->
                <div class="mb-3">
                  <label for="password" class="form-label">رمز عبور</label>
                  <input
                    type="password"
                    class="form-control form-control-lg"
                    id="password"
                    v-model="form.password"
                    placeholder="رمز عبور خود را وارد کنید"
                    required
                  />
                </div>

                <!-- Confirm Password Field -->
                <div class="mb-3">
                  <label for="confirmPassword" class="form-label">تکرار رمز عبور</label>
                  <input
                    type="password"
                    class="form-control form-control-lg"
                    id="confirmPassword"
                    v-model="form.confirmPassword"
                    placeholder="رمز عبور را تکرار کنید"
                    required
                  />
                </div>

                <!-- Terms Checkbox -->
                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="terms" v-model="form.terms" required>
                  <label class="form-check-label" for="terms">
                    <small>
                      با
                      <a href="#" class="text-primary">شرایط و قوانین</a>
                      موافقم
                    </small>
                  </label>
                </div>

                <!-- Submit Button -->
                <div class="d-grid mb-3">
                  <button
                    type="submit"
                    class="btn btn-success btn-lg"
                    :disabled="loading || !form.terms"
                  >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ loading ? 'در حال ثبت نام...' : 'ثبت نام' }}
                  </button>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="alert alert-danger" role="alert">
                  {{ error }}
                </div>

                <!-- Success Message -->
                <div v-if="success" class="alert alert-success" role="alert">
                  {{ success }}
                </div>

                <!-- Links -->
                <div class="text-center">
                  <p class="mb-0">
                    حساب کاربری دارید؟
                    <NuxtLink to="/login" class="text-primary text-decoration-none">
                      وارد شوید
                    </NuxtLink>
                  </p>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const config = useRuntimeConfig()
const router = useRouter()

// Form data
const form = reactive({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  terms: false
})

// State
const loading = ref(false)
const error = ref('')
const success = ref('')

// Handle register
const handleRegister = async () => {
  // Validate passwords match
  if (form.password !== form.confirmPassword) {
    error.value = 'رمز عبور و تکرار آن یکسان نیستند'
    return
  }

  // Validate password length
  if (form.password.length < 6) {
    error.value = 'رمز عبور باید حداقل ۶ کاراکتر باشد'
    return
  }

  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const response = await $fetch(`${config.public.apiBase}auth/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: form.name,
        email: form.email,
        password: form.password
      })
    })

    // Handle successful registration
    if (response.success) {
      success.value = 'ثبت نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.'

      // Clear form
      form.name = ''
      form.email = ''
      form.password = ''
      form.confirmPassword = ''
      form.terms = false

      // Redirect to login after 2 seconds
      setTimeout(() => {
        router.push('/login')
      }, 2000)
    } else {
      error.value = response.message || 'خطا در ثبت نام'
    }
  } catch (err) {
    console.error('Register error:', err)
    error.value = 'خطا در اتصال به سرور. لطفا دوباره تلاش کنید.'
  } finally {
    loading.value = false
  }
}

definePageMeta({
  title: 'ثبت نام - CMS Panel',
  layout: false // Don't use the default layout for register page
})
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.card {
  border-radius: 15px;
}
</style>
