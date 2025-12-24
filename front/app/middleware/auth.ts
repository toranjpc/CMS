export default defineNuxtRouteMiddleware(async (to, from) => {
  const { $auth } = useNuxtApp()
  const auth = $auth || useAuth()

  // Wait for auth initialization
  if (auth.loading.value) {
    await new Promise(resolve => {
      const unwatch = watch(() => auth.loading.value, (loading) => {
        if (!loading) {
          unwatch()
          resolve(void 0)
        }
      })
    })
  }

  // If not authenticated, redirect to login
  if (!auth.isAuthenticated.value) {
    //console.log('🔒 User not authenticated, redirecting to login')
    return navigateTo('/login', { replace: true })
  }

  // Validate token on protected routes
  try {
    const isValid = await auth.validateToken()

    if (!isValid) {
      //console.log('❌ Token invalid, redirecting to login')
      auth.clearAuth()
      return navigateTo('/login', { replace: true })
    }

    //console.log('✅ User authenticated and token valid')
  } catch (error) {
    //console.error('Token validation error in middleware:', error)
    auth.clearAuth()
    return navigateTo('/login', { replace: true })
  }
})

