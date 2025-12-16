import { initializeSecurity } from '~/utils/security'

export default defineNuxtPlugin(() => {
  // Initialize security measures on client side
  initializeSecurity()

  console.log('🔒 Security measures initialized')
})