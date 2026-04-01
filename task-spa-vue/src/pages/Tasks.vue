<template>
  <AppLayout
    :categories="categories"
    :active-category="taskStore.filters.category_id"
    :show-sidebar="true"
    @filter-category="handleCategoryFilter"
    @edit-category="openEditCategory"
  >
    <div>
      <!-- ----------------------------------------------------------------
           Page Header
      ----------------------------------------------------------------- -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Tasks</h1>
          <p class="text-sm text-gray-500 mt-0.5">
            {{ taskStore.pagination.total }} task{{ taskStore.pagination.total !== 1 ? 's' : '' }}
          </p>
        </div>
        <div class="hidden sm:flex items-center gap-3">
          <button @click="openCategoryForm" class="btn-secondary gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m4-4h-8m4-4v8" />
            </svg>
            New Category
          </button>
          <button @click="openCreate" class="btn-primary gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Task
          </button>
        </div>
      </div>

      <!-- ----------------------------------------------------------------
           Filter Bar
      ----------------------------------------------------------------- -->
      <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6 flex flex-wrap gap-3">
        <!-- Search -->
        <div class="relative flex-1 min-w-[200px]">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchInput"
            type="text"
            placeholder="Search tasks…"
            class="form-input pl-9"
          />
        </div>

        <!-- Status filter -->
        <select
          v-model="statusFilter"
          @change="applyStatusFilter"
          class="form-input w-auto min-w-[140px]"
        >
          <option value="">All statuses</option>
          <option value="todo">To Do</option>
          <option value="in_progress">In Progress</option>
          <option value="done">Done</option>
          <option value="overdue">Overdue</option>
        </select>

        <!-- Category filter (mobile — sidebar hidden) -->
        <select
          v-model="categoryFilter"
          @change="handleCategoryFilter(categoryFilter)"
          class="form-input w-auto min-w-[140px] lg:hidden"
        >
          <option value="">All categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>

        <!-- Clear filters -->
        <button
          v-if="hasActiveFilters"
          @click="clearFilters"
          class="btn-secondary text-sm"
        >
          Clear filters
        </button>

        <button
          @click="openCategoryForm"
          class="btn-secondary text-sm sm:hidden"
        >
          New Category
        </button>

        <button
          v-if="selectedCategory"
          @click="openEditCategory(selectedCategory)"
          class="btn-secondary text-sm lg:hidden"
        >
          Edit Category
        </button>
      </div>

      <!-- ----------------------------------------------------------------
           Task Grid
      ----------------------------------------------------------------- -->

      <!-- Loading skeletons -->
      <div v-if="taskStore.loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <SkeletonCard v-for="i in 3" :key="i" />
      </div>

      <!-- Empty state -->
      <div
        v-else-if="!taskStore.tasks.length"
        class="flex flex-col items-center justify-center py-20 text-center"
      >
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 mb-1">No tasks found</h3>
        <p class="text-sm text-gray-400 mb-4">
          {{ hasActiveFilters ? 'Try adjusting your filters.' : 'Create your first task to get started.' }}
        </p>
        <button v-if="!hasActiveFilters" @click="openCreate" class="btn-primary">
          Create Task
        </button>
      </div>

      <!-- Task cards -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <TaskCard
          v-for="task in taskStore.tasks"
          :key="task.id"
          :task="task"
          @edit="openEdit(task)"
          @deleted="onTaskDeleted"
        />
      </div>

      <!-- ----------------------------------------------------------------
           Pagination
      ----------------------------------------------------------------- -->
      <Pagination
        v-if="taskStore.pagination.last_page > 1"
        :meta="taskStore.pagination"
        class="mt-6"
        @page-change="handlePageChange"
      />
    </div>

    <!-- ----------------------------------------------------------------
         Floating Action Button (mobile)
    ----------------------------------------------------------------- -->
    <button
      @click="openCreate"
      class="fixed bottom-6 right-6 sm:hidden w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg
             flex items-center justify-center hover:bg-blue-700 transition-colors z-20"
      aria-label="Add task"
    >
      <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
    </button>

    <!-- ----------------------------------------------------------------
         Task Form Modal
    ----------------------------------------------------------------- -->
    <TaskForm
      v-if="showForm"
      :task="editingTask"
      :categories="categories"
      @saved="onFormSaved"
      @close="closeForm"
    />

    <CategoryForm
      v-if="showCategoryForm"
      :category="editingCategory"
      @saved="onCategorySaved"
      @close="closeCategoryForm"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import CategoryForm from '../components/CategoryForm.vue'
import TaskCard from '../components/TaskCard.vue'
import TaskForm from '../components/TaskForm.vue'
import Pagination from '../components/Pagination.vue'
import SkeletonCard from '../components/SkeletonCard.vue'
import { useTaskStore } from '../stores/tasks.js'
import api from '../api/axios.js'

const taskStore = useTaskStore()

// ---- Categories ----
const categories = ref([])
const editingCategory = ref(null)

async function loadCategories() {
  try {
    const res = await api.get('/api/categories')
    categories.value = res.data.data
  } catch (_) {
    // silently ignore
  }
}

// ---- Filters ----
const searchInput   = ref(taskStore.filters.search)
const statusFilter  = ref(taskStore.filters.status)
const categoryFilter = ref(taskStore.filters.category_id)

let searchTimer = null
watch(searchInput, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    taskStore.setFilter('search', val)
    taskStore.fetchTasks()
  }, 300)
})

function applyStatusFilter() {
  taskStore.setFilter('status', statusFilter.value)
  taskStore.fetchTasks()
}

function handleCategoryFilter(id) {
  categoryFilter.value = id
  taskStore.setFilter('category_id', id)
  taskStore.fetchTasks()
}

const hasActiveFilters = computed(
  () => !!searchInput.value || !!statusFilter.value || !!categoryFilter.value
)

const selectedCategory = computed(() =>
  categories.value.find((cat) => String(cat.id) === String(categoryFilter.value)) ?? null
)

function clearFilters() {
  searchInput.value    = ''
  statusFilter.value   = ''
  categoryFilter.value = ''
  taskStore.resetFilters()
  taskStore.fetchTasks()
}

// ---- Page change ----
function handlePageChange(page) {
  taskStore.setPage(page)
  taskStore.fetchTasks()
}

// ---- Modal ----
const showForm    = ref(false)
const editingTask = ref(null)
const showCategoryForm = ref(false)

function openCreate() {
  editingTask.value = null
  showForm.value = true
}

function openCategoryForm() {
  editingCategory.value = null
  showCategoryForm.value = true
}

function openEditCategory(category) {
  if (!category) return
  editingCategory.value = { ...category }
  showCategoryForm.value = true
}

function openEdit(task) {
  editingTask.value = task
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  editingTask.value = null
}

function closeCategoryForm() {
  showCategoryForm.value = false
  editingCategory.value = null
}

async function onFormSaved() {
  closeForm()
  await taskStore.fetchTasks()
  await loadCategories()
}

async function onCategorySaved(category) {
  const wasEditing = !!editingCategory.value
  closeCategoryForm()
  await loadCategories()

  if (category?.id && !wasEditing) {
    categoryFilter.value = category.id
    taskStore.setFilter('category_id', category.id)
    await taskStore.fetchTasks()
    return
  }

  if (selectedCategory.value) {
    await taskStore.fetchTasks()
  }
}

async function onTaskDeleted() {
  await taskStore.fetchTasks()
}

// ---- Init ----
onMounted(async () => {
  await Promise.all([taskStore.fetchTasks(), loadCategories()])
})
</script>
