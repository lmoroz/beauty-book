import { defineStore } from 'pinia'
import api from '../api/client.js'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('master_token'),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isMaster: (state) => state.user?.role === 'master',
    isAdmin: (state) => state.user?.role === 'admin',
    masterId: (state) => state.user?.master_id,
  },

  actions: {
    async login(login, password) {
      const { data } = await api.post('/auth/login', { login, password })
      this.token = data.access_token
      this.user = data.user
      localStorage.setItem('master_token', this.token)
    },

    async fetchUser() {
      if (!this.token) return
      try {
        const { data } = await api.get('/auth/me')
        this.user = data
      } catch {
        this.logout()
      }
    },

    logout() {
      this.token = null
      this.user = null
      localStorage.removeItem('master_token')
    },
  },
})
