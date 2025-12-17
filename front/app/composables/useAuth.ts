import { ref, computed, nextTick } from 'vue'
import { navigateTo, useRouter } from 'nuxt/app'
import type { User, AuthState, LoginCredentials, AuthResponse } from '~/types/auth'


export const useAuth = () => {
  const config = useRuntimeConfig()
  const router = useRouter()

  // Reactive state
  const authState = ref<AuthState>({
    user: null,
    token: null,
    isAuthenticated: false,
    loading: true
  })

  // Computed properties
  const isAuthenticated = computed(() => authState.value.isAuthenticated)
  const user = computed(() => authState.value.user)
  const token = computed(() => authState.value.token)
  const loading = computed(() => authState.value.loading)

  // Initialize auth state from localStorage
  const initializeAuth = () => {
    try {
      const storedToken = localStorage.getItem('auth_token')
      const storedUser = localStorage.getItem('auth_user')

      if (storedToken && storedUser) {
        authState.value.token = storedToken
        authState.value.user = JSON.parse(storedUser)
        authState.value.isAuthenticated = true
      }
    } catch (error) {
      console.error('Error initializing auth:', error)
      clearAuth()
    } finally {
      authState.value.loading = false
    }
  }

  // Login function
  const login = async (credentials: LoginCredentials): Promise<AuthResponse> => {
    const { mobile, password, remember = false } = credentials
    authState.value.loading = true

    try {
      // Get CSRF cookie first (Sanctum requirement)
      await $fetch(`${config.public.apiBase}sanctum/csrf-cookie`, {
        method: 'GET',
        credentials: 'include'
      })

      // Login request
      const response = await $fetch(`${config.public.apiBase}auth/login`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          mobile,
          password,
          remember
        })
      }) as any

      if (response.token && response.user) {
        // Store auth data
        authState.value.token = response.token
        authState.value.user = response.user
        authState.value.isAuthenticated = true

        // Persist to localStorage
        localStorage.setItem('auth_token', response.token)
        localStorage.setItem('auth_user', JSON.stringify(response.user))

        // Set Authorization header for future requests
        // Update default headers for $fetch
        useNuxtApp().$fetch = $fetch.create({
          headers: {
            'Authorization': `Bearer ${response.token}`
          }
        })

        return { success: true, user: response.user } as AuthResponse
      } else {
        throw new Error(response.message || 'خطا در ورود به سیستم')
      }
    } catch (error: any) {
      console.error('Login error:', error)

      let errorMessage = 'خطا در اتصال به سرور'

      if (error.response?.status === 422) {
        errorMessage = 'اطلاعات وارد شده صحیح نیست'
      } else if (error.response?.status === 429) {
        errorMessage = 'تعداد تلاش‌های ورود بیش از حد مجاز است. لطفا کمی صبر کنید.'
      } else if (error.response?.data?.message) {
        errorMessage = error.response.data.message
      }

      return { success: false, message: errorMessage } as AuthResponse
    } finally {
      authState.value.loading = false
    }
  }

  // Logout function
  const logout = async () => {
    authState.value.loading = true

    try {
      if (authState.value.token) {
        // Call logout endpoint
        await $fetch(`${config.public.apiBase}auth/logout`, {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Authorization': `Bearer ${authState.value.token}`,
            'Accept': 'application/json'
          }
        })
      }
    } catch (error) {
      console.error('Logout error:', error)
      // Continue with local logout even if API call fails
    } finally {
      clearAuth()

      // Redirect to login
      await nextTick()
      await navigateTo('/login')
    }
  }

  // Validate current token
  const validateToken = async (): Promise<boolean> => {
    if (!authState.value.token) {
      return false
    }

    try {
      const response = await $fetch(`${config.public.apiBase}auth/me`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Authorization': `Bearer ${authState.value.token}`,
          'Accept': 'application/json'
        }
      }) as any

      if (response.user) {
        // Update user data
        authState.value.user = response.user
        localStorage.setItem('auth_user', JSON.stringify(response.user))
        return true
      }

      return false
    } catch (error) {
      console.error('Token validation error:', error)
      clearAuth()
      return false
    }
  }

  // Refresh token
  const refreshToken = async (): Promise<boolean> => {
    try {
      const response = await $fetch(`${config.public.apiBase}auth/refresh`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Authorization': `Bearer ${authState.value.token}`,
          'Accept': 'application/json'
        }
      }) as any

      if (response.token) {
        authState.value.token = response.token
        localStorage.setItem('auth_token', response.token)
        return true
      }

      return false
    } catch (error) {
      console.error('Token refresh error:', error)
      clearAuth()
      return false
    }
  }

  // Clear authentication data
  const clearAuth = () => {
    authState.value.user = null
    authState.value.token = null
    authState.value.isAuthenticated = false
    authState.value.loading = false

    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
  }

  // Check if user has specific permission
  const hasPermission = (permission: string): boolean => {
    if (!authState.value.user?.permissions) return false
    return authState.value.user.permissions.includes(permission)
  }

  // Check if user has specific role
  const hasRole = (role: string): boolean => {
    if (!authState.value.user?.roles) return false
    return authState.value.user.roles.includes(role)
  }

  // Initialize on composable creation
  initializeAuth()

  return {
    // State
    isAuthenticated,
    user,
    token,
    loading,

    // Methods
    login,
    logout,
    validateToken,
    refreshToken,
    clearAuth,
    hasPermission,
    hasRole,
    initializeAuth
  }
}

