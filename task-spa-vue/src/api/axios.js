import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// -----------------------------------------------------------------------
// Request interceptor — ensure token is always sent if available
// -----------------------------------------------------------------------
api.interceptors.request.use((config) => {
  config.headers['Accept'] = 'application/json'

  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }

  return config
})

// -----------------------------------------------------------------------
// Response interceptor — handle 401 globally
// -----------------------------------------------------------------------

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error.response?.status

    // 401 — clear auth and redirect to login
    if (status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')

      // Lazy-import to avoid circular deps at module load time
      const { useAuthStore } = await import('../stores/auth.js')
      const { default: router } = await import('../router/index.js')

      try {
        const authStore = useAuthStore()
        authStore.$reset()
      } catch (_) {
        // pinia may not be ready yet (e.g. during initial fetchUser)
      }

      if (router.currentRoute.value?.name !== 'login') {
        router.push({ name: 'login' })
      }

      return Promise.reject(error)
    }
    return Promise.reject(error)
  }
)

export default api
