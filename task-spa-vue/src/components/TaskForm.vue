<template>
  <!-- Modal backdrop -->
  <Teleport to="body">
    <div
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="$emit('close')"
    >
      <!-- Overlay -->
      <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="$emit('close')"></div>

      <!-- Modal panel -->
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto z-10">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900">
            {{ task ? 'Edit Task' : 'Create Task' }}
          </h2>
          <button
            @click="$emit('close')"
            class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600
                   hover:bg-gray-100 rounded-lg transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="px-6 py-5 space-y-4" novalidate>
          <!-- Title -->
          <div>
            <label for="title" class="form-label">Title <span class="text-red-500">*</span></label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              placeholder="What needs to be done?"
              class="form-input"
              :class="{ 'border-red-400': errors.title }"
              autofocus
            />
            <p v-if="errors.title" class="form-error">{{ errors.title[0] }}</p>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="form-label">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              placeholder="Add more details…"
              class="form-input resize-none"
              :class="{ 'border-red-400': errors.description }"
            ></textarea>
            <p v-if="errors.description" class="form-error">{{ errors.description[0] }}</p>
          </div>

          <!-- Row: status + priority -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="status" class="form-label">Status</label>
              <select id="status" v-model="form.status" class="form-input" :class="{ 'border-red-400': errors.status }">
                <option value="todo">To Do</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
              </select>
              <p v-if="errors.status" class="form-error">{{ errors.status[0] }}</p>
            </div>
            <div>
              <label for="priority" class="form-label">Priority</label>
              <select id="priority" v-model="form.priority" class="form-input" :class="{ 'border-red-400': errors.priority }">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
              <p v-if="errors.priority" class="form-error">{{ errors.priority[0] }}</p>
            </div>
          </div>

          <!-- Category -->
          <div>
            <label for="category_id" class="form-label">Category</label>
            <select id="category_id" v-model="form.category_id" class="form-input" :class="{ 'border-red-400': errors.category_id }">
              <option :value="null">— No category —</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <p v-if="errors.category_id" class="form-error">{{ errors.category_id[0] }}</p>
          </div>

          <!-- Due date -->
          <div>
            <label for="due_date" class="form-label">Due Date</label>
            <input
              id="due_date"
              v-model="form.due_date"
              type="date"
              class="form-input"
              :class="{ 'border-red-400': errors.due_date }"
              :min="today"
            />
            <p v-if="errors.due_date" class="form-error">{{ errors.due_date[0] }}</p>
          </div>

          <!-- Footer buttons -->
          <div class="flex gap-3 pt-2">
            <button type="button" @click="$emit('close')" class="btn-secondary flex-1">
              Cancel
            </button>
            <button type="submit" :disabled="loading" class="btn-primary flex-1 gap-2">
              <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              {{ loading ? 'Saving…' : task ? 'Save Changes' : 'Create Task' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useTaskStore } from '../stores/tasks.js'

const props = defineProps({
  task: {
    type: Object,
    default: null,
  },
  categories: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['saved', 'close'])

const taskStore = useTaskStore()
const loading = ref(false)
const errors  = ref({})

const today = new Date().toISOString().split('T')[0]

const form = reactive({
  title:       props.task?.title       ?? '',
  description: props.task?.description ?? '',
  status:      props.task?.status      ?? 'todo',
  priority:    props.task?.priority    ?? 'medium',
  category_id: props.task?.category?.id ?? null,
  due_date:    props.task?.due_date    ?? '',
})

async function handleSubmit() {
  errors.value = {}
  loading.value = true

  // Build payload — omit empty strings for optional fields
  const payload = { ...form }
  if (!payload.description) payload.description = null
  if (!payload.due_date)    payload.due_date    = null
  if (!payload.category_id) payload.category_id = null

  try {
    if (props.task) {
      await taskStore.updateTask(props.task.id, payload)
    } else {
      await taskStore.createTask(payload)
    }
    emit('saved')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors ?? {}
    }
  } finally {
    loading.value = false
  }
}
</script>
