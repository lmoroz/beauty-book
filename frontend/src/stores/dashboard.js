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

  let eventSource = null
  let currentWeekStart = null
  const sseConnected = ref(false)

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
    if (weekStart) currentWeekStart = weekStart
    loading.value = true
    try {
      const params = currentWeekStart ? { week_start: currentWeekStart } : {}
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

  async function confirmBooking(bookingId) {
    try {
      const { data } = await api.patch('/master/dashboard/confirm-booking', { booking_id: bookingId })
      return data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    }
  }

  async function cancelBooking(bookingId, reason = null) {
    try {
      const payload = { booking_id: bookingId }
      if (reason) payload.reason = reason
      const { data } = await api.patch('/master/dashboard/cancel-booking', payload)
      return data
    } catch (e) {
      error.value = e.response?.data?.message || e.message
      throw e
    }
  }

  function subscribeSSE(masterId) {
    if (eventSource) {
      eventSource.close()
    }

    const baseUrl = api.defaults.baseURL.replace('/api/v1', '')
    const url = `${baseUrl}/api/v1/schedule-events/${masterId}/stream`

    eventSource = new EventSource(url, { withCredentials: true })
    sseConnected.value = true

    eventSource.addEventListener('schedule_update', (event) => {
      try {
        const data = JSON.parse(event.data)
        if (data.action === 'slot_booked' || data.action === 'slot_freed') {
          fetchSchedule()
          fetchStats()
        }
      } catch {
        // ignore parse errors
      }
    })

    eventSource.onerror = () => {
      sseConnected.value = false
      eventSource.close()
      setTimeout(() => {
        if (masterId) subscribeSSE(masterId)
      }, 5000)
    }
  }

  function unsubscribeSSE() {
    if (eventSource) {
      eventSource.close()
      eventSource = null
    }
    sseConnected.value = false
  }

  return {
    stats,
    bookings,
    schedule,
    services,
    profile,
    loading,
    error,
    sseConnected,
    fetchStats,
    fetchBookings,
    fetchSchedule,
    fetchServices,
    fetchProfile,
    updateProfile,
    toggleSlot,
    fetchBookingDetail,
    confirmBooking,
    cancelBooking,
    subscribeSSE,
    unsubscribeSSE,
  }
})

