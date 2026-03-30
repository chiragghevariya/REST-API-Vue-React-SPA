import { defineStore } from 'pinia'
import api from '../api/axios.js'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('auth_user') ?? 'null'),
    token: localStorage.getItem('auth_token') ?? null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
  },

  actions: {
    // -----------------------------------------------------------------
    // Persist helpers
    // -----------------------------------------------------------------
    _persist() {
      localStorage.setItem('auth_user', JSON.stringify(this.user))
      localStorage.setItem('auth_token', this.token)
    },

    _clear() {
      this.user = null
      this.token = null
      localStorage.removeItem('auth_user')
      localStorage.removeItem('auth_token')
    },

    // -----------------------------------------------------------------
    // Register
    // -----------------------------------------------------------------
    async register(data) {
      const response = await api.post('/api/register', data)
      this.token = response.data.token
      this.user  = response.data.data
      this._persist()
      return response
    },

    // -----------------------------------------------------------------
    // Login
    // -----------------------------------------------------------------
    async login(credentials) {
      const response = await api.post('/api/login', credentials)
      this.token = response.data.token
      this.user  = response.data.data
      this._persist()
      return response
    },

    // -----------------------------------------------------------------
    // Logout
    // -----------------------------------------------------------------
    async logout() {
      try {
        await api.post('/api/logout')
      } finally {
        this._clear()
      }
    },

    // -----------------------------------------------------------------
    // Fetch current user (used on app boot + route guard)
    // -----------------------------------------------------------------
    async fetchUser() {
      if (!this.token) return null
      try {
        const response = await api.get('/api/user')
        this.user = response.data.data
        this._persist()
        return this.user
      } catch {
        this._clear()
        return null
      }
    },
  },
})
