<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
    <!-- Results info -->
    <p class="text-sm text-gray-500 order-2 sm:order-1">
      <template v-if="meta.from && meta.to">
        Showing <span class="font-medium text-gray-700">{{ meta.from }}</span>
        to <span class="font-medium text-gray-700">{{ meta.to }}</span>
        of <span class="font-medium text-gray-700">{{ meta.total }}</span> results
      </template>
      <template v-else>
        {{ meta.total }} result{{ meta.total !== 1 ? 's' : '' }}
      </template>
    </p>

    <!-- Page buttons -->
    <nav class="flex items-center gap-1 order-1 sm:order-2" aria-label="Pagination">
      <!-- Previous -->
      <button
        @click="$emit('page-change', meta.current_page - 1)"
        :disabled="meta.current_page <= 1"
        class="w-8 h-8 flex items-center justify-center rounded-lg border text-sm transition-colors
               disabled:opacity-40 disabled:cursor-not-allowed border-gray-200 text-gray-600 hover:bg-gray-50"
        aria-label="Previous page"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <!-- Page pills -->
      <template v-for="page in visiblePages" :key="page">
        <span v-if="page === '...'" class="w-8 h-8 flex items-center justify-center text-gray-400 text-sm">
          &hellip;
        </span>
        <button
          v-else
          @click="$emit('page-change', page)"
          class="w-8 h-8 flex items-center justify-center rounded-lg border text-sm font-medium transition-colors"
          :class="page === meta.current_page
            ? 'bg-blue-600 border-blue-600 text-white'
            : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
          :aria-current="page === meta.current_page ? 'page' : undefined"
        >
          {{ page }}
        </button>
      </template>

      <!-- Next -->
      <button
        @click="$emit('page-change', meta.current_page + 1)"
        :disabled="meta.current_page >= meta.last_page"
        class="w-8 h-8 flex items-center justify-center rounded-lg border text-sm transition-colors
               disabled:opacity-40 disabled:cursor-not-allowed border-gray-200 text-gray-600 hover:bg-gray-50"
        aria-label="Next page"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </nav>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  meta: {
    type: Object,
    required: true,
    // { current_page, last_page, from, to, total }
  },
})

defineEmits(['page-change'])

/**
 * Build the array of page numbers/ellipses to display.
 * Always shows first, last, current, and 1 neighbour on each side.
 */
const visiblePages = computed(() => {
  const { current_page: current, last_page: last } = props.meta
  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1)
  }

  const pages = []
  const addPage = (p) => {
    if (!pages.includes(p) && p >= 1 && p <= last) pages.push(p)
  }

  addPage(1)
  addPage(2)
  addPage(current - 1)
  addPage(current)
  addPage(current + 1)
  addPage(last - 1)
  addPage(last)

  pages.sort((a, b) => a - b)

  // Insert ellipses where gaps are > 1
  const result = []
  for (let i = 0; i < pages.length; i++) {
    result.push(pages[i])
    if (i < pages.length - 1 && pages[i + 1] - pages[i] > 1) {
      result.push('...')
    }
  }

  return result
})
</script>
