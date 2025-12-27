import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 10000,
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('master_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('master_token')
    }
    if (error.response?.status === 429) {
      console.warn('Rate limited. Please try again later.')
    }
    return Promise.reject(error)
  },
)

export default api
