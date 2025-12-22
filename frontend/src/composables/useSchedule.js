import { ref } from 'vue'
import api from '../api/client.js'

export function useSchedule() {
  const slots = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchSlots(masterId, date) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/masters/${masterId}/schedule`, {
        params: { date },
      })
      slots.value = data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  return { slots, loading, error, fetchSlots }
}
