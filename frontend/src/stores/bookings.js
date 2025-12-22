import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/client.js'

export const useBookingsStore = defineStore('bookings', () => {
  const currentBooking = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function createBooking(payload) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/bookings', payload)
      currentBooking.value = data
      return data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      return null
    } finally {
      loading.value = false
    }
  }

  async function fetchBooking(id) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/bookings/${id}`)
      currentBooking.value = data
      return data
    } catch (e) {
      error.value = e.message
      return null
    } finally {
      loading.value = false
    }
  }

  async function cancelBooking(id) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.patch(`/bookings/${id}/cancel`)
      currentBooking.value = data
      return data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      return null
    } finally {
      loading.value = false
    }
  }

  return { currentBooking, loading, error, createBooking, fetchBooking, cancelBooking }
})
