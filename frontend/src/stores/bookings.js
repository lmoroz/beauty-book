import { defineStore } from 'pinia'
import api from '../api/client.js'

export const useBookingsStore = defineStore('bookings', {
  state: () => ({
    bookings: [],
    currentBooking: null,
    loading: false,
    error: null,
  }),

  actions: {
    async createBooking(data) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/bookings', data)
        return response.data
      } catch (e) {
        this.error = e.response?.data?.message || 'Ошибка создания записи'
        throw e
      } finally {
        this.loading = false
      }
    },

    async fetchBooking(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get(`/bookings/${id}`)
        this.currentBooking = response.data
      } catch (e) {
        this.error = e.response?.data?.message || 'Запись не найдена'
      } finally {
        this.loading = false
      }
    },
  },
})
