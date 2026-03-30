<template>
  <div class="bg-white rounded-xl border border-gray-200 overflow-hidden animate-pulse">
    <!-- Color accent bar -->
    <div class="h-1 bg-gray-200 w-full"></div>

    <div class="p-4 space-y-3">
      <!-- Status + priority row -->
      <div class="flex items-center justify-between">
        <div class="h-5 w-16 bg-gray-200 rounded-full"></div>
        <div class="h-5 w-12 bg-gray-200 rounded-full"></div>
      </div>

      <!-- Title -->
      <div class="space-y-1.5">
        <div class="h-4 bg-gray-200 rounded w-4/5"></div>
        <div class="h-4 bg-gray-200 rounded w-3/5"></div>
      </div>

      <!-- Description -->
      <div class="space-y-1.5 pt-1">
        <div class="h-3 bg-gray-100 rounded w-full"></div>
        <div class="h-3 bg-gray-100 rounded w-5/6"></div>
      </div>

      <!-- Category + date row -->
      <div class="flex items-center justify-between pt-1">
        <div class="flex items-center gap-1.5">
          <div class="w-2 h-2 bg-gray-200 rounded-full"></div>
          <div class="h-3 w-16 bg-gray-200 rounded"></div>
        </div>
        <div class="h-3 w-14 bg-gray-200 rounded"></div>
      </div>
    </div>
  </div>
</template>
