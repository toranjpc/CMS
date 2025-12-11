import { useAuthStore } from '~/stores/auth'
import axios from 'axios'

export const useAuth = () => {
  const auth = useAuthStore()

  const login = async (email: string, password: string) => {
    const res = await axios.post('http://localhost:8000/api/login', { email, password })
    auth.setUser(res.data.user, res.data.token)
  }

  const register = async (data: any) => {
    const res = await axios.post('http://localhost:8000/api/register', data)
    auth.setUser(res.data.user, res.data.token)
  }

  const logout = () => {
    auth.logout()
  }

  return { auth, login, register, logout }
}
