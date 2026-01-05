import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/client.js'

export const useDashboardStore = defineStore('dashboard', () => {
  const stats = ref(null)
  const bookings = ref([])
  const schedule = ref(null)
  const services = ref([])
  const profile = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchStats() {
    try {
      const { data } = await api.get('/master/dashboard/stats')
      stats.value = data
    } catch (e) {
      error.value = e.message
    }
  }

  async function fetchBookings(status = 'upcoming') {
    loading.value = true
    try {
      const { data } = await api.get('/master/dashboard/bookings', { params: { status } })
      bookings.value = data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchSchedule(weekStart = null) {
    loading.value = true
    try {
      const params = weekStart ? { week_start: weekStart } : {}
      const { data } = await api.get('/master/dashboard/schedule', { params })
      schedule.value = data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchServices() {
    loading.value = true
    try {
      const { data } = await api.get('/master/dashboard/services')
      services.value = data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchProfile() {
    loading.value = true
    try {
      const { data } = await api.get('/master/dashboard/profile')
      profile.value = data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function updateProfile(payload) {
    loading.value = true
    try {
      const { data } = await api.put('/master/dashboard/profile', payload)
      profile.value = data
      return data
    } catch (e) {
      error.value = e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function toggleSlot(slotId, reason = null) {
    try {
      const payload = { slot_id: slotId }
      if (reason) payload.reason = reason
      const { data } = await api.patch('/master/dashboard/toggle-slot', payload)
      return data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    }
  }

  async function fetchBookingDetail(slotId) {
    try {
      const { data } = await api.get('/master/dashboard/booking-detail', { params: { slot_id: slotId } })
      return data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    }
  }

  return {
    stats,
    bookings,
    schedule,
    services,
    profile,
    loading,
    error,
    fetchStats,
    fetchBookings,
    fetchSchedule,
    fetchServices,
    fetchProfile,
    updateProfile,
    toggleSlot,
    fetchBookingDetail,
  }
})
