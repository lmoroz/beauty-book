import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/client.js'

export const useMastersStore = defineStore('masters', () => {
  const masters = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchMasters() {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/masters')
      masters.value = data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchMaster(id) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/masters/${id}`)
      return data
    } catch (e) {
      error.value = e.message
      return null
    } finally {
      loading.value = false
    }
  }

  return { masters, loading, error, fetchMasters, fetchMaster }
})
