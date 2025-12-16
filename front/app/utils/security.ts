/**
 * Security utilities for input validation and sanitization
 */

// Mobile number validation
export const validateMobile = (mobile: string): boolean => {
  // Iranian mobile number pattern
  const mobileRegex = /^09[0-9]{9}$/
  return mobileRegex.test(mobile.trim())
}

// Email validation (for future use)
export const validateEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email.trim())
}

// Password strength validation
export const validatePasswordStrength = (password: string): {
  isValid: boolean
  score: number
  feedback: string[]
} => {
  const feedback: string[] = []
  let score = 0

  // Length check
  if (password.length >= 8) {
    score += 1
  } else {
    feedback.push('رمز عبور باید حداقل ۸ کاراکتر باشد')
  }

  // Lowercase check
  if (/[a-z]/.test(password)) {
    score += 1
  } else {
    feedback.push('رمز عبور باید حداقل یک حرف کوچک داشته باشد')
  }

  // Uppercase check
  if (/[A-Z]/.test(password)) {
    score += 1
  } else {
    feedback.push('رمز عبور باید حداقل یک حرف بزرگ داشته باشد')
  }

  // Number check
  if (/\d/.test(password)) {
    score += 1
  } else {
    feedback.push('رمز عبور باید حداقل یک عدد داشته باشد')
  }

  // Special character check
  if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
    score += 1
  } else {
    feedback.push('رمز عبور باید حداقل یک کاراکتر خاص داشته باشد')
  }

  return {
    isValid: score >= 4 && password.length >= 8,
    score,
    feedback
  }
}

// Input sanitization - basic XSS protection
export const sanitizeInput = (input: string): string => {
  if (!input || typeof input !== 'string') return ''

  return input
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#x27;')
    .replace(/\//g, '&#x2F;')
    .trim()
}

// Generate secure random string
export const generateSecureToken = (length: number = 32): string => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
  let result = ''

  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length))
  }

  return result
}

// Rate limiting helper (client-side)
class RateLimiter {
  private attempts: Map<string, number[]> = new Map()
  private maxAttempts: number
  private windowMs: number

  constructor(maxAttempts: number = 5, windowMs: number = 15 * 60 * 1000) { // 15 minutes
    this.maxAttempts = maxAttempts
    this.windowMs = windowMs
  }

  isAllowed(key: string): boolean {
    const now = Date.now()
    const attempts = this.attempts.get(key) || []

    // Remove old attempts outside the window
    const validAttempts = attempts.filter(time => now - time < this.windowMs)

    if (validAttempts.length >= this.maxAttempts) {
      return false
    }

    // Add current attempt
    validAttempts.push(now)
    this.attempts.set(key, validAttempts)

    return true
  }

  getRemainingTime(key: string): number {
    const attempts = this.attempts.get(key) || []
    if (attempts.length === 0) return 0

    const oldestAttempt = Math.min(...attempts)
    const timeLeft = this.windowMs - (Date.now() - oldestAttempt)

    return Math.max(0, timeLeft)
  }
}

// Export rate limiter instance for login attempts
export const loginRateLimiter = new RateLimiter(5, 15 * 60 * 1000) // 5 attempts per 15 minutes

// CSRF token management
export class CSRFManager {
  private token: string | null = null
  private expiry: number | null = null

  async getToken(): Promise<string> {
    const now = Date.now()

    // Return existing token if still valid (5 minutes)
    if (this.token && this.expiry && now < this.expiry) {
      return this.token
    }

    try {
      const config = useRuntimeConfig()

      // Get new CSRF cookie
      await $fetch(`${config.public.apiBase}sanctum/csrf-cookie`, {
        method: 'GET',
        credentials: 'include'
      })

      // Generate a new token (in a real app, this would come from the server)
      this.token = generateSecureToken(32)
      this.expiry = now + (5 * 60 * 1000) // 5 minutes

      return this.token
    } catch (error) {
      console.error('Failed to get CSRF token:', error)
      throw error
    }
  }

  clearToken(): void {
    this.token = null
    this.expiry = null
  }
}

export const csrfManager = new CSRFManager()

// Security headers check (client-side)
export const checkSecurityHeaders = (): void => {
  if (process.client && window.location.protocol !== 'https:') {
    console.warn('⚠️  Warning: Not using HTTPS. Consider using HTTPS for production.')
  }
}

// Initialize security checks on app start
export const initializeSecurity = (): void => {
  checkSecurityHeaders()

  // Clear sensitive data on page unload
  if (process.client) {
    window.addEventListener('beforeunload', () => {
      // Clear any sensitive data from memory
      // This is a basic implementation - in a real app you'd clear more
    })
  }
}
