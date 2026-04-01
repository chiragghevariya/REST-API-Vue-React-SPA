import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'
import api from '../api/axios.js'

export const useTaskStore = defineStore('tasks', {
  state: () => ({
    tasks: [],
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
      from: null,
      to: null,
    },
    loading: false,
    filters: {
      status: '',
      category_id: '',
      search: '',
      page: 1,
    },
  }),

  getters: {
    totalByStatus: (state) => (status) =>
      state.tasks.filter((t) => t.status === status).length,
  },

  actions: {
    // -----------------------------------------------------------------
    // Pagination normalization
    // -----------------------------------------------------------------
    normalizePagination(meta = {}) {
      const pickValue = (value, fallback = null) => {
        if (Array.isArray(value)) {
          for (let i = value.length - 1; i >= 0; i -= 1) {
            const candidate = pickValue(value[i], fallback)
            if (candidate !== null && candidate !== undefined && candidate !== '') {
              return candidate
            }
          }
          return fallback
        }

        return value ?? fallback
      }

      const toNumber = (value, fallback = 0) => {
        const normalized = pickValue(value, fallback)
        const parsed = Number(normalized)
        return Number.isFinite(parsed) ? parsed : fallback
      }

      const total = toNumber(meta.total, this.tasks.length)
      const perPage = Math.max(1, toNumber(meta.per_page, this.pagination.per_page || 10))
      const currentPage = Math.max(1, toNumber(meta.current_page, this.filters.page || 1))
      const lastPage = Math.max(1, toNumber(meta.last_page, Math.ceil(total / perPage) || 1))

      return {
        current_page: Math.min(currentPage, lastPage),
        last_page: lastPage,
        per_page: perPage,
        total,
        from: pickValue(meta.from, total > 0 ? ((currentPage - 1) * perPage) + 1 : null),
        to: pickValue(meta.to, total > 0 ? Math.min(currentPage * perPage, total) : null),
      }
    },

    // -----------------------------------------------------------------
    // Fetch (with filters)
    // -----------------------------------------------------------------
    async fetchTasks(overrides = {}) {
      this.loading = true
      const params = { ...this.filters, ...overrides }

      // Strip empty values so they don't pollute the query string
      Object.keys(params).forEach((k) => {
        if (params[k] === '' || params[k] === null || params[k] === undefined) {
          delete params[k]
        }
      })

      try {
        const response = await api.get('/api/tasks', { params })
        this.tasks      = response.data.data
        this.pagination = this.normalizePagination(response.data.meta)
        return response.data
      } catch (error) {
        useToast().error('Failed to load tasks.')
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchAllTasks(overrides = {}) {
      this.loading = true
      const baseParams = { ...this.filters, ...overrides, page: 1 }

      Object.keys(baseParams).forEach((k) => {
        if (baseParams[k] === '' || baseParams[k] === null || baseParams[k] === undefined) {
          delete baseParams[k]
        }
      })

      try {
        const firstResponse = await api.get('/api/tasks', { params: baseParams })
        const allTasks = [...firstResponse.data.data]
        const meta = this.normalizePagination(firstResponse.data.meta)

        for (let page = 2; page <= meta.last_page; page += 1) {
          const response = await api.get('/api/tasks', {
            params: { ...baseParams, page },
          })
          allTasks.push(...response.data.data)
        }

        this.tasks = allTasks
        this.pagination = {
          ...meta,
          from: allTasks.length ? 1 : null,
          to: allTasks.length || null,
        }

        return {
          data: allTasks,
          meta: this.pagination,
        }
      } catch (error) {
        useToast().error('Failed to load tasks.')
        throw error
      } finally {
        this.loading = false
      }
    },

    // -----------------------------------------------------------------
    // Create
    // -----------------------------------------------------------------
    async createTask(data) {
      const toast = useToast()
      try {
        const response = await api.post('/api/tasks', data)
        const newTask = response.data.data
        // Optimistic: prepend to current list if on page 1
        if (this.filters.page === 1) {
          this.tasks.unshift(newTask)
          if (this.tasks.length > this.pagination.per_page) {
            this.tasks.pop()
          }
        }
        this.pagination.total += 1
        toast.success('Task created successfully.')
        return newTask
      } catch (error) {
        toast.error('Failed to create task.')
        throw error
      }
    },

    // -----------------------------------------------------------------
    // Update
    // -----------------------------------------------------------------
    async updateTask(id, data) {
      const toast = useToast()
      const index = this.tasks.findIndex((t) => t.id === id)

      // Optimistic update
      const original = index !== -1 ? { ...this.tasks[index] } : null
      if (index !== -1) {
        this.tasks[index] = { ...this.tasks[index], ...data }
      }

      try {
        const response = await api.put(`/api/tasks/${id}`, data)
        if (index !== -1) {
          this.tasks[index] = response.data.data
        }
        toast.success('Task updated successfully.')
        return response.data.data
      } catch (error) {
        // Rollback
        if (index !== -1 && original) {
          this.tasks[index] = original
        }
        toast.error('Failed to update task.')
        throw error
      }
    },

    // -----------------------------------------------------------------
    // Delete
    // -----------------------------------------------------------------
    async deleteTask(id) {
      const toast = useToast()
      const index = this.tasks.findIndex((t) => t.id === id)
      const original = index !== -1 ? { ...this.tasks[index] } : null

      // Optimistic remove
      if (index !== -1) {
        this.tasks.splice(index, 1)
        this.pagination.total = Math.max(0, this.pagination.total - 1)
      }

      try {
        await api.delete(`/api/tasks/${id}`)
        toast.success('Task deleted.')
      } catch (error) {
        // Rollback
        if (index !== -1 && original) {
          this.tasks.splice(index, 0, original)
          this.pagination.total += 1
        }
        toast.error('Failed to delete task.')
        throw error
      }
    },

    // -----------------------------------------------------------------
    // Update status only (PATCH)
    // -----------------------------------------------------------------
    async updateStatus(id, status) {
      const toast = useToast()
      const index = this.tasks.findIndex((t) => t.id === id)
      const originalStatus = index !== -1 ? this.tasks[index].status : null

      // Optimistic
      if (index !== -1) {
        this.tasks[index].status = status
      }

      try {
        const response = await api.patch(`/api/tasks/${id}/status`, { status })
        if (index !== -1) {
          this.tasks[index] = response.data.data
        }
        toast.success(`Task marked as ${status.replace('_', ' ')}.`)
        return response.data.data
      } catch (error) {
        // Rollback
        if (index !== -1 && originalStatus !== null) {
          this.tasks[index].status = originalStatus
        }
        toast.error('Failed to update task status.')
        throw error
      }
    },

    // -----------------------------------------------------------------
    // Filter helpers
    // -----------------------------------------------------------------
    setFilter(key, value) {
      this.filters[key] = value
      this.filters.page = 1
    },

    setPage(page) {
      this.filters.page = page
    },

    resetFilters() {
      this.filters = { status: '', category_id: '', search: '', page: 1 }
    },
  },
})
