<template>
  <AppLayout>
    <div>
      <!-- Page header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Overview of your tasks</p>
      </div>

      <!-- Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard
          label="Total Tasks"
          :value="stats.total"
          color="blue"
          icon="clipboard-list"
        />
        <StatCard
          label="To Do"
          :value="stats.todo"
          color="gray"
          icon="circle"
        />
        <StatCard
          label="In Progress"
          :value="stats.inProgress"
          color="amber"
          icon="clock"
        />
        <StatCard
          label="Done"
          :value="stats.done"
          color="green"
          icon="check-circle"
        />
      </div>

      <!-- Second row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <!-- Overdue -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Overdue Tasks</p>
              <p class="text-2xl font-bold text-red-600">{{ stats.overdue }}</p>
            </div>
          </div>
          <RouterLink
            v-if="stats.overdue > 0"
            to="/tasks"
            class="text-sm text-red-600 font-medium hover:underline"
          >
            View overdue tasks &rarr;
          </RouterLink>
          <p v-else class="text-sm text-gray-400">No overdue tasks. Great work!</p>
        </div>

        <!-- Completion rate -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Completion Rate</p>
              <p class="text-2xl font-bold text-green-600">{{ completionRate }}%</p>
            </div>
          </div>
          <!-- Progress bar -->
          <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div
              class="h-full bg-green-500 rounded-full transition-all duration-500"
              :style="{ width: completionRate + '%' }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Recent tasks -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Recent Tasks</h2>
          <RouterLink to="/tasks" class="text-sm text-blue-600 font-medium hover:underline">
            View all
          </RouterLink>
        </div>

        <div v-if="taskStore.loading" class="p-4 space-y-3">
          <SkeletonCard v-for="i in 3" :key="i" />
        </div>

        <ul v-else-if="recentTasks.length" class="divide-y divide-gray-50">
          <li
            v-for="task in recentTasks"
            :key="task.id"
            class="px-5 py-3 hover:bg-gray-50 transition-colors"
          >
            <RouterLink :to="`/tasks/${task.id}`" class="flex items-center gap-3">
              <span
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                :class="statusClass(task.status)"
              >
                {{ statusLabel(task.status) }}
              </span>
              <span class="flex-1 text-sm text-gray-800 truncate">{{ task.title }}</span>
              <span
                v-if="task.is_overdue"
                class="text-xs text-red-500 font-medium"
              >
                Overdue
              </span>
            </RouterLink>
          </li>
        </ul>

        <div v-else class="px-5 py-8 text-center text-gray-400 text-sm">
          No tasks yet.
          <RouterLink to="/tasks" class="text-blue-600 font-medium hover:underline ml-1">Create one</RouterLink>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import SkeletonCard from '../components/SkeletonCard.vue'
import { useTaskStore } from '../stores/tasks.js'

// ---- Inline StatCard to avoid a separate file ----
import { defineComponent, h } from 'vue'
const StatCard = defineComponent({
  props: ['label', 'value', 'color', 'icon'],
  setup(props) {
    const colorMap = {
      blue:  'bg-blue-50 text-blue-600',
      gray:  'bg-gray-100 text-gray-600',
      amber: 'bg-amber-50 text-amber-600',
      green: 'bg-green-50 text-green-600',
    }
    return () =>
      h('div', { class: 'bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4' }, [
        h('div', { class: `w-12 h-12 rounded-xl flex items-center justify-center ${colorMap[props.color] || colorMap.blue}` }, [
          h('span', { class: 'text-2xl font-bold' }, props.value),
        ]),
        h('div', [
          h('p', { class: 'text-sm text-gray-500' }, props.label),
          h('p', { class: 'text-2xl font-bold text-gray-900' }, props.value),
        ]),
      ])
  },
})

const taskStore = useTaskStore()

const stats = computed(() => {
  const all = taskStore.tasks
  return {
    total:      taskStore.pagination.total || all.length,
    todo:       all.filter((t) => t.status === 'todo').length,
    inProgress: all.filter((t) => t.status === 'in_progress').length,
    done:       all.filter((t) => t.status === 'done').length,
    overdue:    all.filter((t) => t.is_overdue).length,
  }
})

const completionRate = computed(() => {
  const total = stats.value.total
  if (!total) return 0
  return Math.round((stats.value.done / total) * 100)
})

const recentTasks = computed(() => taskStore.tasks.slice(0, 5))

function statusClass(status) {
  return {
    todo:        'bg-gray-100 text-gray-600',
    in_progress: 'bg-amber-100 text-amber-700',
    done:        'bg-green-100 text-green-700',
  }[status] ?? 'bg-gray-100 text-gray-600'
}

function statusLabel(status) {
  return { todo: 'To Do', in_progress: 'In Progress', done: 'Done' }[status] ?? status
}

onMounted(() => {
  taskStore.fetchTasks()
})
</script>
