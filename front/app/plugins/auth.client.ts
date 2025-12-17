export default defineNuxtPlugin(() => {
  // Make auth composable available globally
  return {
    provide: {
      auth: useAuth()
    }
  }
})

