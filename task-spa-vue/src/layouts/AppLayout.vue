<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- ----------------------------------------------------------------
         Top Navigation Bar
    ----------------------------------------------------------------- -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo / App Name -->
          <div class="flex items-center gap-3">
            <RouterLink to="/tasks" class="flex items-center gap-2">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
              </div>
              <span class="font-bold text-gray-900 text-lg hidden sm:block">Task Manager</span>
            </RouterLink>

            <!-- Desktop nav links -->
            <nav class="hidden md:flex items-center gap-1 ml-4">
              <RouterLink
                to="/dashboard"
                class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path === '/dashboard'
                  ? 'bg-blue-50 text-blue-700'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
              >
                Dashboard
              </RouterLink>
              <RouterLink
                to="/tasks"
                class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                :class="$route.path.startsWith('/tasks')
                  ? 'bg-blue-50 text-blue-700'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
              >
                Tasks
              </RouterLink>
            </nav>
          </div>

          <!-- Right: user + logout -->
          <div class="flex items-center gap-3">
            <span class="hidden sm:block text-sm text-gray-600 font-medium truncate max-w-[160px]">
              {{ authStore.user?.name }}
            </span>

            <button
              @click="handleLogout"
              :disabled="loggingOut"
              class="btn-secondary text-sm px-3 py-1.5 gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span class="hidden sm:inline">{{ loggingOut ? 'Signing out…' : 'Sign out' }}</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- ----------------------------------------------------------------
         Main layout: sidebar + content
    ----------------------------------------------------------------- -->
    <div class="flex flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
      <!-- Sidebar (categories) -->
      <aside v-if="showSidebar" class="hidden lg:block w-56 shrink-0">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-24">
          <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Categories</h3>
          </div>
          <nav class="p-2">
            <button
              @click="$emit('filter-category', '')"
              class="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-colors"
              :class="!activeCategory ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50'"
            >
              <span class="w-2 h-2 rounded-full bg-gray-400"></span>
              All Tasks
            </button>
            <div
              v-for="cat in categories"
              :key="cat.id"
              class="group flex items-center gap-1"
            >
              <button
                @click="$emit('filter-category', cat.id)"
                class="flex flex-1 items-center gap-2 px-3 py-2 text-sm rounded-lg transition-colors"
                :class="activeCategory === cat.id ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50'"
              >
                <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: cat.color }"></span>
                <span class="truncate">{{ cat.name }}</span>
                <span class="ml-auto text-xs text-gray-400">{{ cat.tasks_count }}</span>
              </button>
              <button
                @click.stop="$emit('edit-category', cat)"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700"
                :aria-label="`Edit ${cat.name}`"
                title="Edit category"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
            </div>
          </nav>
        </div>
      </aside>

      <!-- Page content slot -->
      <main class="flex-1 min-w-0">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
  activeCategory: {
    type: [String, Number],
    default: '',
  },
  showSidebar: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['filter-category', 'edit-category'])

const authStore = useAuthStore()
const router = useRouter()
const loggingOut = ref(false)

async function handleLogout() {
  loggingOut.value = true
  try {
    await authStore.logout()
    router.push({ name: 'login' })
  } finally {
    loggingOut.value = false
  }
}
</script>
