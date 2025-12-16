import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null
  }),
  actions: {
    setUser(user: any, token: string) {
      this.user = user
      this.token = token
    },
    logout() {
      this.user = null
      this.token = null
    }
  }
})
