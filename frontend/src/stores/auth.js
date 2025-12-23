import { defineStore } from 'pinia'
import api from '../api/client.js'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    master: null,
    token: localStorage.getItem('master_token'),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async login(email, password) {
      const response = await api.post('/master/login', { email, password })
      this.token = response.data.token
      this.master = response.data.master
      localStorage.setItem('master_token', this.token)
    },

    logout() {
      this.token = null
      this.master = null
      localStorage.removeItem('master_token')
    },
  },
})
