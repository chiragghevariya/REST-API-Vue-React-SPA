<template>
  <AppLayout>
    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <svg class="animate-spin h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
    </div>

    <!-- Not found -->
    <div v-else-if="!task" class="text-center py-20">
      <p class="text-gray-500">Task not found.</p>
      <RouterLink to="/tasks" class="text-blue-600 font-medium hover:underline mt-2 block">
        Back to tasks
      </RouterLink>
    </div>

    <!-- Task Detail -->
    <div v-else>
      <!-- Back link + actions -->
      <div class="flex items-center justify-between mb-6">
        <RouterLink to="/tasks" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to tasks
        </RouterLink>
        <div class="flex gap-2">
          <button @click="showEditForm = true" class="btn-secondary gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </button>
          <button @click="confirmDelete" class="btn-danger gap-1.5" :disabled="deleting">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            {{ deleting ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-100">
          <div class="flex flex-wrap items-start gap-3 mb-2">
            <span
              class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
              :class="statusClass"
            >
              {{ statusLabel }}
            </span>
            <span
              class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
              :class="priorityClass"
            >
              {{ task.priority }} priority
            </span>
            <span
              v-if="task.is_overdue"
              class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700"
            >
              Overdue
            </span>
          </div>
          <h1 class="text-xl font-bold text-gray-900">{{ task.title }}</h1>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Description -->
          <div class="md:col-span-2">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Description</h2>
            <p v-if="task.description" class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap">
              {{ task.description }}
            </p>
            <p v-else class="text-gray-400 text-sm italic">No description provided.</p>
          </div>

          <!-- Meta -->
          <div class="space-y-4">
            <div v-if="task.category">
              <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Category</h3>
              <CategoryBadge :name="task.category.name" :color="task.category.color" />
            </div>

            <div v-if="task.due_date">
              <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Due Date</h3>
              <p class="text-sm" :class="task.is_overdue ? 'text-red-600 font-medium' : 'text-gray-700'">
                {{ formattedDate(task.due_date) }}
              </p>
            </div>

            <div>
              <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Created</h3>
              <p class="text-sm text-gray-700">{{ formattedDate(task.created_at) }}</p>
            </div>

            <div>
              <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Last Updated</h3>
              <p class="text-sm text-gray-700">{{ formattedDate(task.updated_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Quick Status Change -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-2">
          <span class="text-sm font-medium text-gray-600 self-center">Quick update:</span>
          <button
            v-for="s in statuses"
            :key="s.value"
            @click="changeStatus(s.value)"
            :disabled="task.status === s.value || updatingStatus"
            class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors disabled:opacity-40"
            :class="task.status === s.value
              ? 'border-blue-500 bg-blue-50 text-blue-700'
              : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-100'"
          >
            {{ s.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <TaskForm
      v-if="showEditForm && task"
      :task="task"
      :categories="categories"
      @saved="onTaskSaved"
      @close="showEditForm = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import TaskForm from '../components/TaskForm.vue'
import CategoryBadge from '../components/CategoryBadge.vue'
import { useTaskStore } from '../stores/tasks.js'
import { useToast } from 'vue-toastification'
import api from '../api/axios.js'

const route = useRoute()
const router = useRouter()
const taskStore = useTaskStore()
const toast = useToast()

const task         = ref(null)
const categories   = ref([])
const loading      = ref(true)
const deleting     = ref(false)
const updatingStatus = ref(false)
const showEditForm = ref(false)

const statuses = [
  { value: 'todo',        label: 'To Do' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'done',        label: 'Done' },
]

const statusClass = computed(() => ({
  todo:        'bg-gray-100 text-gray-700',
  in_progress: 'bg-amber-100 text-amber-700',
  done:        'bg-green-100 text-green-700',
}[task.value?.status] ?? 'bg-gray-100 text-gray-600'))

const statusLabel = computed(() =>
  ({ todo: 'To Do', in_progress: 'In Progress', done: 'Done' }[task.value?.status] ?? '')
)

const priorityClass = computed(() => ({
  low:    'bg-gray-100 text-gray-600',
  medium: 'bg-amber-100 text-amber-700',
  high:   'bg-red-100 text-red-700',
}[task.value?.priority] ?? 'bg-gray-100 text-gray-600'))

function formattedDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: 'numeric', month: 'long', day: 'numeric',
  })
}

async function fetchTask() {
  loading.value = true
  try {
    const res = await api.get(`/api/tasks/${route.params.id}`)
    task.value = res.data.data
  } catch (err) {
    if (err.response?.status === 404 || err.response?.status === 403) {
      task.value = null
    }
  } finally {
    loading.value = false
  }
}

async function fetchCategories() {
  try {
    const res = await api.get('/api/categories')
    categories.value = res.data.data
  } catch (_) {}
}

async function changeStatus(status) {
  updatingStatus.value = true
  try {
    const updated = await taskStore.updateStatus(task.value.id, status)
    task.value = updated
  } finally {
    updatingStatus.value = false
  }
}

async function confirmDelete() {
  if (!confirm('Are you sure you want to delete this task? This cannot be undone.')) return
  deleting.value = true
  try {
    await taskStore.deleteTask(task.value.id)
    router.push('/tasks')
  } finally {
    deleting.value = false
  }
}

async function onTaskSaved() {
  showEditForm.value = false
  await fetchTask()
}

onMounted(() => {
  Promise.all([fetchTask(), fetchCategories()])
})
</script>
