<template>
  <div class="space-y-10 animate-pulse">
    <!-- Header Skeleton -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-4">
        <div class="w-24 h-8 bg-slate-100 dark:bg-slate-800 rounded-full"></div>
        <div class="w-32 h-8 bg-slate-100 dark:bg-slate-800 rounded-lg"></div>
      </div>
      <div class="w-20 h-6 bg-slate-100 dark:bg-slate-800 rounded"></div>
    </div>

    <!-- Title & Description Skeleton -->
    <div class="space-y-4">
      <div class="w-3/4 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl"></div>
      <div class="space-y-2">
        <div class="w-full h-4 bg-slate-100 dark:bg-slate-800 rounded"></div>
        <div class="w-full h-4 bg-slate-100 dark:bg-slate-800 rounded"></div>
        <div class="w-2/3 h-4 bg-slate-100 dark:bg-slate-800 rounded"></div>
      </div>
    </div>

    <!-- Details Grid Skeleton -->
    <div class="grid grid-cols-2 gap-8 py-6 border-y border-slate-50 dark:border-slate-800/50">
      <div class="space-y-4">
        <div class="space-y-2">
          <div class="w-20 h-3 bg-slate-50 dark:bg-slate-800/50 rounded"></div>
          <div class="w-full h-12 bg-slate-50 dark:bg-slate-800/50 rounded-xl"></div>
        </div>
        <div class="space-y-2">
          <div class="w-20 h-3 bg-slate-50 dark:bg-slate-800/50 rounded"></div>
          <div class="w-full h-12 bg-slate-50 dark:bg-slate-800/50 rounded-xl"></div>
        </div>
      </div>
      <div class="space-y-4">
        <div class="space-y-2">
          <div class="w-20 h-3 bg-slate-50 dark:bg-slate-800/50 rounded"></div>
          <div class="w-full h-12 bg-slate-50 dark:bg-slate-800/50 rounded-xl"></div>
        </div>
      </div>
    </div>

    <!-- Subtasks Skeleton -->
    <div class="space-y-4">
      <div class="w-24 h-4 bg-slate-100 dark:bg-slate-800 rounded"></div>
      <div class="space-y-3">
        <div v-for="i in 3" :key="i" class="flex items-center gap-3">
          <div class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800"></div>
          <div class="flex-1 h-5 bg-slate-100 dark:bg-slate-800 rounded"></div>
        </div>
      </div>
    </div>
  </div>
</template>
