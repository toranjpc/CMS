<template>
  <div class="login-page">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card shadow-lg border-0">
            <div class="card-body p-5">
              <div class="text-center mb-4">
                <h2 class="fw-bold">ورود به سیستم</h2>
                <p class="text-muted">برای ادامه لطفا وارد شوید</p>
              </div>

              <form @submit.prevent="handleLogin">
                <!-- Mobile Field -->
                <div class="mb-3">
                  <label for="mobile" class="form-label">شماره موبایل</label>
                  <input
                    type="tel"
                    class="form-control form-control-lg"
                    id="mobile"
                    v-model="form.mobile"
                    placeholder="09123456789"
                    pattern="^09[0-9]{9}$"
                    maxlength="11"
                    required
                  />
                  <div class="form-text">
                    شماره موبایل باید با 09 شروع شود و 11 رقم باشد
                  </div>
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
                    minlength="8"
                    required
                  />
                  <div class="form-text">
                    رمز عبور باید حداقل 8 کاراکتر باشد
                  </div>
                </div>

                <!-- Remember Me -->
                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="remember" v-model="form.remember">
                  <label class="form-check-label" for="remember">
                    مرا به خاطر بسپار
                  </label>
                </div>

                <!-- Submit Button -->
                <div class="d-grid mb-3">
                  <button
                    type="submit"
                    class="btn btn-primary btn-lg"
                    :disabled="authStore.loading || loading"
                  >
                    <span v-if="authStore.loading || loading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ (authStore.loading || loading) ? 'در حال ورود...' : 'ورود' }}
                  </button>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="alert alert-danger" role="alert">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  {{ error }}
                </div>

                <!-- Success Message -->
                <div v-if="success" class="alert alert-success" role="alert">
                  <i class="bi bi-check-circle me-2"></i>
                  {{ success }}
                </div>

                <!-- Links -->
                <div class="text-center">
                  <p class="mb-0">
                    حساب کاربری ندارید؟
                    <NuxtLink to="/register" class="text-primary text-decoration-none">
                      ثبت نام کنید
                    </NuxtLink>
                  </p>
                </div>
              </form>
            </div>
          </div>

          <!-- Demo Credentials -->
          <div class="card mt-3 border-warning">
            <div class="card-body">
              <h6 class="card-title text-warning">
                <i class="bi bi-info-circle me-1"></i>
                اطلاعات آزمایشی
              </h6>
              <p class="card-text small mb-1">
                <strong>موبایل:</strong> 09123456789
              </p>
              <p class="card-text small mb-0">
                <strong>رمز عبور:</strong> password123
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { validateMobile, loginRateLimiter } from '~/utils/security'

const authStore = useAuth()
const router = useRouter()

// Form data
const form = reactive({
  mobile: '',
  password: '',
  remember: false
})

// Local state
const loading = ref(false)
const error = ref('')
const success = ref('')

// Use imported validation function

// Validate form
const validateForm = () => {
  if (!form.mobile.trim()) {
    return 'شماره موبایل الزامی است'
  }

  if (!validateMobile(form.mobile)) {
    return 'فرمت شماره موبایل صحیح نیست'
  }

  if (!form.password.trim()) {
    return 'رمز عبور الزامی است'
  }

  if (form.password.length < 8) {
    return 'رمز عبور باید حداقل 8 کاراکتر باشد'
  }

  return null
}

// Handle login
const handleLogin = async () => {
  error.value = ''
  success.value = ''

  // Check rate limiting
  if (!loginRateLimiter.isAllowed('login')) {
    const remainingTime = Math.ceil(loginRateLimiter.getRemainingTime('login') / 1000 / 60)
    error.value = `تعداد تلاش‌های ورود بیش از حد مجاز است. لطفا ${remainingTime} دقیقه صبر کنید.`
    return
  }

  // Validate form
  const validationError = validateForm()
  if (validationError) {
    error.value = validationError
    return
  }

  loading.value = true

  try {
    const result = await authStore.login({
      mobile: form.mobile,
      password: form.password,
      remember: form.remember
    })

    if (result.success) {
      success.value = 'ورود با موفقیت انجام شد. در حال انتقال...'

      // Redirect to dashboard after a short delay
      setTimeout(async () => {
        await router.push('/dashboard')
      }, 1000)
    } else {
      error.value = result.error || 'خطا در ورود به سیستم'
    }
  } catch (err) {
    console.error('Login error:', err)

    if (err.response?.status === 422) {
      error.value = 'اطلاعات وارد شده صحیح نیست'
    } else if (err.response?.status === 429) {
      error.value = 'تعداد تلاش‌های ورود بیش از حد مجاز است. لطفا ۵ دقیقه صبر کنید.'
    } else if (err.response?.status === 500) {
      error.value = 'خطای سرور. لطفا دوباره تلاش کنید.'
    } else {
      error.value = 'خطا در اتصال به سرور. لطفا اتصال اینترنت خود را بررسی کنید.'
    }
  } finally {
    loading.value = false
  }
}

// Clear messages when form changes
watch(() => [form.mobile, form.password], () => {
  error.value = ''
  success.value = ''
})

definePageMeta({
  title: 'ورود - CMS Panel',
  layout: false, // Don't use the default layout for login page
  middleware: 'guest' // Use guest middleware to redirect authenticated users
})
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.card {
  border-radius: 15px;
}
</style>
