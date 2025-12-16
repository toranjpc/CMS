export interface User {
  id: number
  name: string
  mobile: string
  email?: string
  roles?: string[]
  permissions?: string[]
  created_at?: string
  updated_at?: string
}

export interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  loading: boolean
}

export interface LoginCredentials {
  mobile: string
  password: string
  remember?: boolean
}

export interface AuthResponse {
  success: boolean
  token?: string
  user?: User
  message?: string
  errors?: Record<string, string[]>
}

export interface ValidationResult {
  isValid: boolean
  score: number
  feedback: string[]
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
  status?: number
}