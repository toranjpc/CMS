import { useAuth } from '~/components/useAuth'

export default defineNuxtPlugin(() => {
  // Make auth composable available globally
  return {
    provide: {
      auth: useAuth()
    }
  }
})

