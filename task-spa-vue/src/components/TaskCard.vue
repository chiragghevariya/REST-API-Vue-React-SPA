<template>
  <div
    class="relative bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md
           transition-all duration-150 group overflow-hidden"
  >
    <!-- Top color accent -->
    <div
      class="h-1 w-full"
      :class="{
        'bg-gray-300':   task.status === 'todo',
        'bg-amber-400':  task.status === 'in_progress',
        'bg-green-500':  task.status === 'done',
      }"
    ></div>

    <div class="p-4">
      <!-- Row 1: status + priority -->
      <div class="flex items-center justify-between mb-2">
        <span
          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
          :class="statusClass"
        >
          {{ statusLabel }}
        </span>
        <span
          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
          :class="priorityClass"
        >
          {{ task.priority }}
        </span>
      </div>

      <!-- Title -->
      <RouterLink :to="`/tasks/${task.id}`">
        <h3 class="font-semibold text-gray-900 text-sm leading-snug line-clamp-2 hover:text-blue-600 transition-colors mb-1">
          {{ task.title }}
        </h3>
      </RouterLink>

      <!-- Description (clamped to 2 lines) -->
      <p
        v-if="task.description"
        class="text-xs text-gray-500 line-clamp-2 mb-3 leading-relaxed"
      >
        {{ task.description }}
      </p>

      <!-- Row 3: category + due date -->
      <div class="flex items-center justify-between gap-2 mt-auto">
        <CategoryBadge
          v-if="task.category"
          :name="task.category.name"
          :color="task.category.color"
        />
        <span v-else class="text-xs text-gray-300">No category</span>

        <span
          v-if="task.due_date"
          class="text-xs font-medium shrink-0"
          :class="task.is_overdue ? 'text-red-500' : 'text-gray-400'"
        >
          {{ formattedDate }}
        </span>
      </div>
    </div>

    <!-- Hover action buttons -->
    <div
      class="absolute top-3 right-3 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
    >
      <button
        @click.prevent="$emit('edit', task)"
        class="w-7 h-7 bg-white border border-gray-200 rounded-lg flex items-center justify-center
               text-gray-400 hover:text-blue-600 hover:border-blue-300 transition-colors shadow-sm"
        title="Edit"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
      </button>
      <button
        @click.prevent="handleDelete"
        :disabled="deleting"
        class="w-7 h-7 bg-white border border-gray-200 rounded-lg flex items-center justify-center
               text-gray-400 hover:text-red-600 hover:border-red-300 transition-colors shadow-sm disabled:opacity-40"
        title="Delete"
      >
        <svg v-if="deleting" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import CategoryBadge from './CategoryBadge.vue'
import { useTaskStore } from '../stores/tasks.js'

const props = defineProps({
  task: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['edit', 'deleted'])

const taskStore = useTaskStore()
const deleting = ref(false)

const statusClass = computed(() => ({
  todo:        'bg-gray-100 text-gray-600',
  in_progress: 'bg-amber-100 text-amber-700',
  done:        'bg-green-100 text-green-700',
}[props.task.status] ?? 'bg-gray-100 text-gray-600'))

const statusLabel = computed(() =>
  ({ todo: 'To Do', in_progress: 'In Progress', done: 'Done' }[props.task.status] ?? props.task.status)
)

const priorityClass = computed(() => ({
  low:    'bg-gray-100 text-gray-500',
  medium: 'bg-amber-50 text-amber-600',
  high:   'bg-red-50 text-red-600',
}[props.task.priority] ?? 'bg-gray-100 text-gray-500'))

const formattedDate = computed(() => {
  if (!props.task.due_date) return null
  return new Date(props.task.due_date).toLocaleDateString(undefined, {
    month: 'short',
    day: 'numeric',
  })
})

async function handleDelete() {
  if (!confirm(`Delete "${props.task.title}"?`)) return
  deleting.value = true
  try {
    await taskStore.deleteTask(props.task.id)
    emit('deleted', props.task.id)
  } finally {
    deleting.value = false
  }
}
</script>
