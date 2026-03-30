<template>
  <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700">
    <span
      class="w-2 h-2 rounded-full flex-shrink-0"
      :style="{ backgroundColor: color || '#6B7280' }"
    ></span>
    <span class="truncate max-w-[100px]">{{ name }}</span>
  </span>
</template>

<script setup>
defineProps({
  name: {
    type: String,
    required: true,
  },
  color: {
    type: String,
    default: '#6B7280',
  },
})
</script>
