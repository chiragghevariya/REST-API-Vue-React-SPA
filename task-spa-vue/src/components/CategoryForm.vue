<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="$emit('close')"
    >
      <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="$emit('close')"></div>

      <div class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
          <h2 class="text-lg font-semibold text-gray-900">{{ isEditing ? 'Edit Category' : 'Create Category' }}</h2>
          <button
            @click="$emit('close')"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4 px-6 py-5" novalidate>
          <div>
            <label for="category_name" class="form-label">Name <span class="text-red-500">*</span></label>
            <input
              id="category_name"
              v-model="form.name"
              type="text"
              maxlength="255"
              placeholder="Work"
              class="form-input"
              :class="{ 'border-red-400': errors.name }"
              autofocus
            />
            <p v-if="errors.name" class="form-error">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label for="category_color" class="form-label">Color</label>
            <div class="flex items-center gap-3">
              <input
                id="category_color"
                v-model="form.color"
                type="color"
                class="h-11 w-14 cursor-pointer rounded-lg border border-gray-300 bg-white p-1"
              />
              <input
                v-model="form.color"
                type="text"
                placeholder="#3B82F6"
                class="form-input"
                :class="{ 'border-red-400': errors.color }"
              />
            </div>
            <p v-if="errors.color" class="form-error">{{ errors.color[0] }}</p>
          </div>

          <div class="flex gap-3 pt-2">
            <button type="button" @click="$emit('close')" class="btn-secondary flex-1">
              Cancel
            </button>
            <button type="submit" :disabled="loading" class="btn-primary flex-1 gap-2">
              <svg v-if="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              {{ loading ? (isEditing ? 'Saving…' : 'Creating…') : (isEditing ? 'Save Changes' : 'Create Category') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useToast } from 'vue-toastification'
import api from '../api/axios.js'

const props = defineProps({
  category: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['saved', 'close'])

const toast = useToast()
const loading = ref(false)
const errors = ref({})
const isEditing = computed(() => !!props.category)
const form = reactive({
  name: props.category?.name ?? '',
  color: props.category?.color ?? '#3B82F6',
})

async function handleSubmit() {
  errors.value = {}
  loading.value = true

  try {
    const payload = {
      name: form.name.trim(),
      color: form.color || null,
    }

    const response = isEditing.value
      ? await api.put(`/api/categories/${props.category.id}`, payload)
      : await api.post('/api/categories', payload)

    toast.success(isEditing.value ? 'Category updated successfully.' : 'Category created successfully.')
    emit('saved', response.data.data)
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors ?? {}
    } else {
      toast.error(isEditing.value ? 'Failed to update category.' : 'Failed to create category.')
    }
  } finally {
    loading.value = false
  }
}
</script>
