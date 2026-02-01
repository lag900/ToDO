<template>
  <div class="p-6 transition-all duration-500 min-h-screen bg-slate-50/50 dark:bg-slate-950/50">
    <header class="mb-10 space-y-6 px-2">
      <!-- Primary Row: Core Navigation -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-6 w-full sm:w-auto">
          <img src="https://i.postimg.cc/jqQRtc95/thinker-(1).png" alt="THINKER Logo" class="h-10 lg:h-14 w-auto object-contain">
          <ContextSwitcher @create="openCreateModal" />
        </div>

        <!-- Desktop View Switcher (Hidden on mobile, preserved as essential) -->
        <div v-if="!isMobile" class="flex items-center bg-white dark:bg-slate-900 p-1 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">
          <button 
            @click="currentView = 'board'" 
            :class="[currentView === 'board' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200']"
            class="px-5 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-colors duration-200"
          >
            Board
          </button>
          <button 
            @click="currentView = 'list'" 
            :class="[currentView === 'list' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200']"
            class="px-5 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-colors duration-200"
          >
            List
          </button>
          <button 
            @click="currentView = 'calendar'" 
            :class="[currentView === 'calendar' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200']"
            class="px-5 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-colors duration-200"
          >
            Calendar
          </button>
        </div>

        <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
          <!-- Mobile Filters Sheet Toggle -->
          <button v-if="isMobile" @click="showMobileSheet = true" class="p-2 sm:hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16v2.17a2 2 0 0 1-.58 1.42L13 14v5l-2 2v-7L4.58 7.59A2 2 0 0 1 4 6.17V4z"/></svg>
          </button>

          <div class="flex items-center gap-3 bg-white dark:bg-slate-900 p-1.5 pr-4 rounded-xl border border-slate-100 dark:border-slate-800 shadow-sm">

            <img :src="auth.user?.avatar || 'https://ui-avatars.com/api/?name=' + auth.user?.display_name" class="w-8 h-8 rounded-full object-cover">
            <div class="hidden lg:block">
              <p class="text-sm font-bold text-slate-800 dark:text-white leading-none">{{ auth.user?.display_name }}</p>
            </div>
            <div class="flex items-center gap-1 border-l border-slate-100 dark:border-slate-800 ml-2 pl-2">
              <router-link v-if="userRole === 'owner' && !workspaceStore.globalMode" to="/settings" class="p-1.5 text-slate-300 hover:text-indigo-600 transition-colors" title="Workspace Settings">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
              </router-link>
              <button @click="auth.logout" class="p-1.5 text-slate-300 hover:text-rose-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 8v-2a2 2 0 0 0-2-2h-7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"></path><path d="M9 12h12l-3-3"></path><path d="M18 15l3-3"></path></svg>
              </button>
            </div>
          </div>
          
          <button v-if="userRole !== 'viewer' && !workspaceStore.globalMode" @click="openModal" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors shadow-lg shadow-indigo-500/20 font-bold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            <span class="hidden sm:inline">New Task</span>
          </button>
          
          <div v-else-if="workspaceStore.globalMode" class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
             <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">View Only Mode</p>
          </div>
        </div>
      </div>

      <!-- Secondary Row: Filters & Global Controls (Hidden on mobile) -->
      <div v-if="!isMobile" class="flex items-center gap-4 pt-4 border-t border-slate-100 dark:border-slate-800/50">
          <button 
            @click="workspaceStore.toggleGlobalMode()"
            class="flex items-center gap-2 px-4 py-2 rounded-xl transition-colors border"
            :class="workspaceStore.globalMode ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white dark:bg-slate-900 text-slate-400 border-slate-200 dark:border-slate-800 hover:text-indigo-600'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span class="text-[10px] font-black uppercase tracking-widest">Global View</span>
          </button>

          <!-- Filter Button (Always Visible) -->
          <button 
            @click="showFiltersDrawer = true"
            class="flex items-center gap-2 px-4 py-2 rounded-xl border transition-colors"
            :class="hasActiveFilters 
              ? 'bg-indigo-50 border-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:border-indigo-500/20' 
              : 'bg-white dark:bg-slate-900 text-slate-400 border-slate-200 dark:border-slate-800 hover:text-indigo-600'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16v2.17a2 2 0 0 1-.58 1.42L13 14v5l-2 2v-7L4.58 7.59A2 2 0 0 1 4 6.17V4z"/></svg>
            <span class="text-[10px] font-black uppercase tracking-widest">Filter</span>
            <div v-if="hasActiveFilters" class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
          </button>

          <!-- Share Button (Shown only if not global and owner) -->
          <button 
            v-if="currentWorkspace && userRole === 'owner' && !workspaceStore.globalMode"
            @click="showShareModal = true"
            class="flex items-center gap-2 px-4 py-2 rounded-xl border bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 border-indigo-100 dark:border-indigo-500/20 hover:bg-indigo-600 hover:text-white transition-colors"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
            <span class="text-[10px] font-black uppercase tracking-widest">Share</span>
          </button>


      </div>
    </header>

    <!-- Right Side Filters Drawer (Desktop) -->
    <div v-if="!isMobile">
      <div 
        v-if="showFiltersDrawer" 
        class="fixed inset-0 z-[10000]"
        @click="showFiltersDrawer = false"
      >
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div 
          @click.stop
          class="absolute right-0 top-0 bottom-0 w-96 bg-white dark:bg-slate-900 shadow-2xl border-l border-slate-100 dark:border-slate-800 p-10 overflow-y-auto animate-in slide-in-from-right duration-300"
        >
          <div class="flex items-center justify-between mb-10">
            <div>
              <h2 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-widest">Filters</h2>
              <p class="text-[10px] font-black uppercase text-slate-400 mt-1">Refine your view</p>
            </div>
            <button @click="showFiltersDrawer = false" class="p-2 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <div class="space-y-10">
            <!-- Context Selector (Only in Global Mode) -->
            <div v-if="workspaceStore.globalMode" class="space-y-4">
              <div class="flex items-center justify-between px-1">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Context</p>
              </div>
              <select 
                v-model="filters.workspaceId" 
                class="w-full h-14 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 text-sm font-bold shadow-sm outline-none transition-all"
              >
                <option :value="null">All Contexts</option>
                <option v-for="ws in workspaceStore.workspaces" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
              </select>
            </div>

            <!-- Priority Selector -->
            <div class="space-y-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Priority</p>
              <div class="grid grid-cols-2 gap-2">
                <button 
                  v-for="p in [null, 'low', 'medium', 'high', 'urgent']" :key="p"
                  @click="filters.priority = p"
                  class="h-12 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all"
                  :class="[
                    filters.priority === p 
                      ? 'bg-indigo-600 text-white border-indigo-600 shadow-md translate-y-[-2px]' 
                      : 'bg-slate-50 dark:bg-slate-800 text-slate-500 border-transparent hover:border-slate-200 dark:hover:border-slate-700'
                  ]"
                >
                  {{ p || 'All Priorities' }}
                </button>
              </div>
            </div>

            <!-- Time Range Presets -->
            <div class="space-y-4">
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Timeline</p>
              <div class="grid grid-cols-1 gap-2">
                <button 
                  v-for="opt in rangeOptions" :key="opt.id"
                  @click="filters.timeRange = opt.id; filters.monthYear = ''"
                  class="h-12 rounded-xl border flex items-center justify-between px-6 transition-all"
                  :class="[
                    filters.timeRange === opt.id 
                      ? 'bg-indigo-50 border-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:border-indigo-500/30 font-black' 
                      : 'bg-white dark:bg-slate-900 text-slate-500 border-slate-100 dark:border-slate-800'
                  ]"
                >
                  <span class="text-[10px] uppercase tracking-widest">{{ opt.label }}</span>
                  <div v-if="filters.timeRange === opt.id" class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse"></div>
                </button>
              </div>
            </div>

            <!-- Custom Range -->
            <div class="space-y-4">
              <div 
                @click="filters.timeRange = 'custom'"
                class="p-6 rounded-2xl border cursor-pointer transition-all"
                :class="filters.timeRange === 'custom' ? 'bg-indigo-50 border-indigo-200 dark:bg-indigo-500/5 dark:border-indigo-500/20' : 'bg-slate-50 dark:bg-slate-800 border-transparent'"
              >
                <div class="flex items-center gap-3 mb-4">
                  <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  </div>
                  <span class="text-[10px] font-black uppercase tracking-widest text-slate-800 dark:text-white">Custom Range</span>
                </div>
                
                <div v-if="filters.timeRange === 'custom'" class="grid grid-cols-2 gap-4 animate-in fade-in slide-in-from-top-2 duration-300">
                  <div class="space-y-1">
                    <p class="text-[8px] font-black uppercase text-slate-400 px-1">From</p>
                    <input type="date" v-model="filters.customStart" class="w-full bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl px-3 py-2 text-[10px] font-bold shadow-sm">
                  </div>
                  <div class="space-y-1">
                    <p class="text-[8px] font-black uppercase text-slate-400 px-1">To</p>
                    <input type="date" v-model="filters.customEnd" class="w-full bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl px-3 py-2 text-[10px] font-bold shadow-sm">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-12 flex items-center gap-3">
            <button @click="resetFilters" class="flex-1 py-4 rounded-2xl border border-slate-100 dark:border-slate-800 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">Reset All</button>
            <button @click="showFiltersDrawer = false" class="flex-[2] py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-500/20 active:scale-95 transition-all">Apply Refinement</button>
          </div>
        </div>
      </div>
    </div>


    <!-- Mobile Navigation indicators -->
    <div v-if="isMobile && currentView === 'board'" class="flex justify-center gap-4 mb-6 sticky top-0 py-2 bg-slate-50/80 dark:bg-slate-950/80 backdrop-blur-md z-30">
       <button 
         v-for="(col, index) in columns" :key="col.id"
         @click="activeMobileColumnIndex = index"
         class="py-2 px-1 focus:outline-none"
       >
          <div 
            class="h-1.5 rounded-full transition-all duration-300"
            :class="activeMobileColumnIndex === index ? 'w-8 bg-indigo-600' : 'w-2 bg-slate-300 dark:bg-slate-700'"
          ></div>
       </button>
    </div>

    <!-- Content Views -->
    <div 
      v-if="currentView === 'board'" 
      class="flex lg:grid lg:grid-cols-4 gap-8 transition-all duration-500 overflow-x-auto lg:overflow-x-visible pb-4 lg:pb-0"
      :class="{'snap-x snap-mandatory': isMobile}"
      @touchstart="handleTouchStart"
      @touchend="handleTouchEnd"
    >
      <div v-for="(column, index) in columns" :key="column.id" 
           v-show="!isMobile || activeMobileColumnIndex === index"
           @dragover.prevent="draggingOverColumn = column.id" 
           @dragleave="draggingOverColumn = null"
           @drop="onDrop($event, column.id)"
           class="flex-shrink-0 w-full lg:w-auto snap-center"
           :class="[
             'rounded-[2.5rem] p-4 sm:p-6 min-h-[500px] lg:min-h-[700px] transition-all duration-500 border-2 border-dashed',
             draggingOverColumn === column.id 
               ? 'bg-slate-200/80 dark:bg-slate-800/80 border-indigo-400 shadow-2xl z-10' 
               : 'bg-slate-100/40 dark:bg-slate-900/40 border-slate-200/50 dark:border-slate-800/50'

           ]">

        
        <header class="flex items-center justify-between mb-8 px-2">
          <div class="flex items-center gap-3">
            <div :class="['w-10 h-10 rounded-2xl flex items-center justify-center shadow-lg', column.bgClass]">
              <span :class="['w-3 h-3 rounded-full', column.dotClass]"></span>
            </div>
            <div>
              <h3 class="text-sm font-heading font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest">
                {{ column.name }}
              </h3>
              <p class="text-[12px] text-slate-400 font-bold uppercase tracking-tighter">{{ filteredTasks.filter(t => t.status === column.id).length }} Tasks</p>
            </div>
          </div>
        </header>
        
        <div class="space-y-5">
          <template v-for="task in filteredTasks.filter(t => t.status === column.id)" :key="task.id">


            <div 
                 :draggable="canDrag"
                 @dragstart="onDragStart($event, task)"
                 @dragend="draggingOverColumn = null"
                 class="bg-white dark:bg-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-100 dark:border-slate-700/50 hover:border-indigo-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-300 cursor-pointer group relative overflow-hidden">
            
              <div :class="['absolute left-0 top-0 bottom-0 w-1', column.dotClass]"></div>

              <div class="flex justify-between items-start mb-4">
                <span :class="priorityClass(task.priority)" class="text-[12px] uppercase font-black px-3 py-1 rounded-full tracking-widest">
                  {{ task.priority }}
                </span>
                <div class="flex items-center gap-2">
                  <div class="flex items-center -space-x-1.5 flex-row-reverse justify-end">
                    <!-- Working By (Top of stack) -->
                    <img 
                      v-if="task.working_by"
                      :src="task.working_by.avatar || 'https://ui-avatars.com/api/?name=' + task.working_by.display_name" 
                      :title="'Working: ' + task.working_by.display_name" 
                      class="w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-800 border-2 border-indigo-500 hover:z-20 hover:scale-110 transition-all object-cover relative z-30"
                    >
                    <!-- Assignee -->
                    <img 
                      v-if="task.assignee && (!task.working_by || task.assignee.id !== task.working_by.id)"
                      :src="task.assignee?.avatar || 'https://ui-avatars.com/api/?name=' + task.assignee?.display_name" 
                      :title="'Assigned to: ' + task.assignee?.display_name" 
                      class="w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-800 hover:z-20 hover:scale-110 transition-all object-cover relative z-20"
                    >
                    <!-- Assigner (Only if different) -->
                    <img 
                      v-if="task.assigned_by && task.assigned_by.id !== task.creator?.id && task.assigned_by.id !== task.assignee?.id && (!task.working_by || task.assigned_by.id !== task.working_by.id)"
                      :src="task.assigned_by.avatar || 'https://ui-avatars.com/api/?name=' + task.assigned_by.display_name" 
                      :title="'Assigned by: ' + task.assigned_by.display_name" 
                      class="w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-800 hover:z-20 hover:scale-110 transition-all object-cover relative z-15"
                    >
                    <!-- Creator -->
                    <img 
                      v-if="task.creator && (!task.assignee || task.assignee.id !== task.creator.id) && (!task.working_by || task.creator.id !== task.working_by.id)"
                      :src="task.creator?.avatar || 'https://ui-avatars.com/api/?name=' + (task.creator?.display_name || 'System')" 
                      :title="'Created by: ' + (task.creator?.display_name || 'System')" 
                      class="w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-800 hover:z-20 hover:scale-110 transition-all object-cover"
                    >
                  </div>
                  
                  <!-- Delete Action (Hover) -->
                  <button 
                    v-if="canDeleteTask(task)"
                    @click.stop="confirmDelete(task)"
                    class="opacity-0 group-hover:opacity-100 p-1.5 text-slate-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-all"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </div>

              <div class="flex items-start justify-between gap-2" @click="selectedTaskId = task.id">
                <div class="flex-1">
                  <h4 class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 transition-colors mb-2 leading-tight text-lg">{{ task.title }}</h4>
                  
                  <!-- Work by Indicator -->
                  <div v-if="column.id === 'in_progress' && task.working_by" class="flex items-center gap-1.5 mb-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600/80 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-md">
                      Work by · {{ task.working_by.display_name }}
                    </span>
                  </div>

                  <!-- Assigned by Indicator -->
                  <div v-if="task.assigned_by" class="flex items-center gap-1.5 mb-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-50 dark:bg-slate-800/50 px-2 py-0.5 rounded-md">
                      Assigned by · {{ task.assigned_by.display_name }}
                    </span>
                  </div>

                  <div v-if="workspaceStore.globalMode" class="mb-3 flex items-center gap-2">
                     <span class="text-[9px] font-black uppercase tracking-[0.15em] px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-lg flex items-center gap-1.5 ring-1 ring-slate-200 dark:ring-slate-700 shadow-sm transition-all group-hover:bg-indigo-50 dark:group-hover:bg-indigo-500/10 group-hover:text-indigo-600 group-hover:ring-indigo-100 dark:group-hover:ring-indigo-500/30">
                        <span class="text-xs">{{ getWorkspaceIcon(task.board?.plan?.workspace?.type) }}</span>
                        {{ task.board?.plan?.workspace?.name }}
                     </span>
                  </div>

                  <!-- Delivery Badge on Card -->
                  <div v-if="task.status === 'done' && task.deliveries?.length > 0" class="mb-2 flex items-center gap-1.5">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] bg-emerald-500 text-white px-2.5 py-1 rounded-lg shadow-lg shadow-emerald-500/20 flex items-center gap-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"/></svg>
                      Delivered
                    </span>
                  </div>
                  <div v-if="task.status === 'done' && (!task.deliveries || task.deliveries.length === 0)" class="mb-2 flex items-center gap-1.5">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2.5 py-1 rounded-lg border border-amber-200 dark:border-amber-500/20">
                      Done · Needs Delivery
                    </span>
                  </div>

                </div>
                
                <button 
                  v-if="hasExtraDetails(task)"
                  @click.stop="toggleExpand(task.id)"
                  class="p-1 text-slate-300 hover:text-indigo-600 transition-all transform"
                  :class="{'rotate-180': expandedTasks.includes(task.id)}"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m6 9 6 6 6-6"/></svg>
                </button>
              </div>
              
              <p v-if="task.description && !expandedTasks.includes(task.id)" class="text-sm text-slate-400 dark:text-slate-500 line-clamp-1 italic leading-relaxed mb-4">{{ task.description }}</p>

              <transition name="expand">
                <div v-if="expandedTasks.includes(task.id)" class="mt-4 space-y-4 border-t border-slate-50 dark:border-slate-700/50 pt-4 animate-in slide-in-from-top-2">
                   <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ task.description || 'No description provided.' }}</p>
                   
                   <div v-if="task.subtasks?.length" class="space-y-1.5">
                      <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Sub-tasks</p>
                      <div v-for="sub in task.subtasks" :key="sub.id" class="flex items-center gap-2 text-[11px] font-bold text-slate-500 group/sub">
                         <button 
                           @click.stop="toggleSubtask(task, sub)"
                           class="w-4 h-4 rounded-md border-2 border-slate-300 dark:border-slate-600 flex items-center justify-center transition-all hover:border-indigo-500"
                         >
                            <div v-if="sub.status === 'done'" class="w-2 h-2 rounded-sm bg-emerald-500"></div>
                         </button>
                         <span :class="{'line-through opacity-50': sub.status === 'done'}">{{ sub.title }}</span>
                      </div>
                   </div>

                   <div v-if="task.checklists?.length" class="flex items-center gap-2">
                      <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Checklist</p>
                      <div class="h-1 flex-1 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                         <div class="h-full bg-indigo-500 transition-all duration-500" :style="{width: checklistProgress(task) + '%'}"></div>
                      </div>
                      <span class="text-[11px] font-black text-slate-400">{{ task.checklists.filter(c => c.is_completed).length }}/{{ task.checklists.length }}</span>
                   </div>
                </div>
              </transition>
              
              <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-50 dark:border-slate-700/50">
                <div class="flex items-center gap-3">
                   <div v-if="task.subtasks?.length" class="flex items-center gap-1.5 px-2 py-1 bg-slate-50 dark:bg-slate-800/80 rounded-lg group/sub transition-all" :title="subtaskTooltip(task)">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" :class="allSubtasksDone(task) ? 'text-emerald-500' : 'text-slate-400'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 2v20"/><path d="m17 7-5-5-5 5"/><path d="m17 17-5 5-5-5"/></svg>
                      <span class="text-[9px] font-black uppercase tracking-widest" :class="allSubtasksDone(task) ? 'text-emerald-500' : 'text-slate-400'">{{ task.subtasks.filter(s => s.status === 'done').length }}/{{ task.subtasks.length }}</span>
                   </div>

                   <div v-if="task.deadline" class="flex items-center gap-1.5 text-[12px] font-black text-slate-400 uppercase tracking-widest">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                      <span>{{ formatDeadline(task.deadline) }}</span>
                   </div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">{{ formatDate(task.created_at) }}</div>
                </div>
              </div>
            </div>
          </template>

          <div v-if="filteredTasks.filter(t => t.status === column.id).length === 0" 
               class="py-12 flex flex-col items-center justify-center text-slate-300 dark:text-slate-700 space-y-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 opacity-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            <p class="text-sm font-black uppercase tracking-widest opacity-50">Empty Slot</p>
          </div>
        </div>
      </div>
    </div>

    <!-- List View -->
    <div v-else-if="currentView === 'list'" class="w-full">
        <!-- Desktop List View -->
        <div v-if="!isMobile" class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl p-8 border border-slate-100 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
               <thead>
                 <tr class="text-[12px] font-heading font-black uppercase tracking-widest text-slate-400 border-b border-slate-50 dark:border-slate-800">
                   <th class="pb-6 px-4">Task Name</th>
                   <th class="pb-6 px-4">Status</th>
                   <th class="pb-6 px-4">Priority</th>
                   <th class="pb-6 px-4">People</th>
                   <th class="pb-6 px-4">Due Date</th>
                   <th class="pb-6 px-4 text-right">Actions</th>
                 </tr>
               </thead>
               <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                   <tr v-for="task in filteredTasks" :key="task.id" @click="selectedTaskId = task.id" class="group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="py-6 px-4">
                       <p class="font-bold text-slate-800 dark:text-white group-hover:text-indigo-600 transition-colors">{{ task.title }}</p>
                       <p v-if="workspaceStore.globalMode" class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-1">
                          {{ task.board?.plan?.workspace?.name }} · {{ task.board?.plan?.workspace?.type }}
                       </p>
                    </td>
                    <td class="py-6 px-4">
                                              <span class="text-[12px] font-black uppercase tracking-tight py-1 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 w-fit block mb-1">{{ task.status.replace('_', ' ') }}</span>
                       <template v-if="task.status === 'done'">
                          <span v-if="task.deliveries?.length > 0" class="text-[9px] font-black uppercase text-emerald-500 font-bold tracking-widest pl-1">Delivered</span>
                          <span v-else class="text-[9px] font-black uppercase text-amber-500 font-bold tracking-widest pl-1">Pending Delivery</span>
                       </template>
                    </td>
                    <td class="py-6 px-4">
                       <span :class="priorityClass(task.priority)" class="text-[12px] font-black uppercase tracking-widest px-3 py-1 rounded-full">{{ task.priority }}</span>
                    </td>
                      <td class="py-6 px-4">
                         <div class="flex items-center -space-x-2">
                             <!-- Creator -->
                             <img :title="'Creator: ' + task.creator?.display_name" :src="task.creator?.avatar || 'https://ui-avatars.com/api/?name=' + task.creator?.display_name" class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-900 object-cover shadow-sm">
                             <!-- Assigner -->
                             <img v-if="task.assigned_by && task.assigned_by.id !== task.creator?.id" :title="'Assigned by: ' + task.assigned_by.display_name" :src="task.assigned_by.avatar || 'https://ui-avatars.com/api/?name=' + task.assigned_by.display_name" class="w-8 h-8 rounded-full border-2 border-white dark:border-slate-900 object-cover shadow-sm z-10">
                             <!-- Worker -->
                             <img v-if="task.working_by" :title="'Working: ' + task.working_by.display_name" :src="task.working_by.avatar || 'https://ui-avatars.com/api/?name=' + task.working_by.display_name" class="w-8 h-8 rounded-full border-2 border-indigo-500 object-cover shadow-md z-20">
                         </div>
                         <p v-if="task.assigned_by" class="text-[8px] font-black uppercase tracking-tighter text-slate-400 mt-1">By: {{ task.assigned_by.display_name }}</p>
                      </td>
                    <td class="py-6 px-4 text-sm font-bold text-slate-400">{{ formatDate(task.deadline || task.created_at) }}</td>
                    <td class="py-6 px-4 text-right">
                       <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                          <button 
                            v-if="getRoleForTask(task) !== 'viewer'"
                            @click.stop="selectedTaskId = task.id"
                            class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-xl transition-all"
                            title="Edit Task"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                          </button>
                          <button 
                            v-if="canDeleteTask(task)"
                            @click.stop="confirmDelete(task)"
                            class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all"
                            title="Delete Task"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                          </button>
                       </div>
                    </td>
                   </tr>
               </tbody>
            </table>
        </div>

        <!-- Mobile Task Cards Layer -->
        <div v-else class="space-y-4 px-2">
            <div 
              v-for="task in filteredTasks" :key="task.id" 
              @touchstart="handleTaskTouchStart"
              @touchend="handleTaskTouchEnd($event, task)"
              class="relative bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 shadow-xl border border-slate-100 dark:border-slate-800 transition-all active:scale-[0.98] overflow-hidden"
            >
                <div class="flex flex-col gap-4">
                  <!-- Header: Status & Priority -->
                  <div class="flex items-center justify-between">
                    <span :class="priorityClass(task.priority)" class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-sm">
                      {{ task.priority }}
                    </span>
<div class="text-right">
                      <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-50 dark:bg-slate-800 px-3 py-1 rounded-lg block">{{ task.status.replace('_', ' ') }}</span>
                      <template v-if="task.status === 'done'">
                         <span v-if="task.delivery" class="text-[8px] font-black uppercase text-emerald-500 mt-1">Delivered</span>
                         <span v-else class="text-[8px] font-black uppercase text-amber-500 mt-1">Pending Delivery</span>
                      </template>
                    </div>
                  </div>

                  <!-- Content: Title & Work indicator -->
                  <div class="space-y-2">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white leading-tight">{{ task.title }}</h3>
                    
                    <div v-if="task.working_by" class="flex items-center gap-2">
                      <img :src="task.working_by.avatar || 'https://ui-avatars.com/api/?name=' + task.working_by.display_name" class="w-6 h-6 rounded-full border-2 border-indigo-500 shadow-sm">
                      <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Work by · {{ task.working_by.display_name }}</span>
                    </div>

                    <div v-if="task.assigned_by" class="flex items-center gap-2">
                       <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-50 dark:bg-slate-800 px-2 py-0.5 rounded">Assigned by · {{ task.assigned_by.display_name }}</span>
                    </div>
                  </div>

                  <!-- Footer: Meta Info -->
                  <div class="flex items-center justify-between pt-4 border-t border-slate-50 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                      <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ formatDate(task.deadline || task.created_at) }}</span>
                    </div>
                    <div class="flex items-center -space-x-1.5">
                       <img :src="task.creator?.avatar || 'https://ui-avatars.com/api/?name=' + task.creator?.display_name" :title="'Created by: ' + (task.creator?.display_name || 'System')" class="w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-900 grayscale">
                    </div>
                  </div>
                </div>

                <!-- Swipe Indicators (Hidden, visual only during swipe) -->
                <div class="absolute inset-y-0 -left-16 w-16 bg-emerald-500 flex items-center justify-center text-white transition-opacity opacity-0">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredTasks.length === 0" class="py-20 text-center bg-white dark:bg-slate-900 rounded-[3rem] border-2 border-dashed border-slate-100 dark:border-slate-800">
               <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
               </div>
               <p class="text-sm font-black uppercase tracking-[0.2em] text-slate-400">No tasks found</p>
            </div>
        </div>
    </div>


    <!-- Calendar View -->
    <CalendarView 
      v-else-if="currentView === 'calendar'" 
      :tasks="filteredTasks" 
      :is-mobile="isMobile" 
      :global-mode="workspaceStore.globalMode"
      v-model:monthYear="filters.monthYear" 
      @task-click="id => selectedTaskId = id" 
    />


    <!-- Task Details Drawer -->
    <TaskDetails v-if="selectedTaskId" :taskId="selectedTaskId" :role="userRole" @close="selectedTaskId = null" @updated="fetchTasks" />

    <!-- Enhanced Task Modal -->
    <div v-show="showModal" class="fixed inset-0 bg-slate-900/90 backdrop-blur-xl flex items-center justify-center p-4 z-[9999]">
      <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl w-full max-w-md p-10 border border-slate-200 dark:border-slate-800 transition-all duration-500 relative overflow-hidden">
        
        <!-- Suggestion Banner -->
        <transition name="fade">
          <div v-if="suggestion" class="absolute top-0 left-0 right-0 bg-indigo-600 text-white px-6 py-2 flex items-center justify-between z-10">
             <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span class="text-[10px] font-black uppercase tracking-widest">{{ suggestion.message }}</span>
             </div>
             <button @click="applySuggestion" class="text-[10px] font-black underline uppercase tracking-widest">Apply</button>
          </div>
        </transition>

        <div class="mb-8 pt-4">
            <h2 class="text-3xl font-black text-slate-800 dark:text-white mb-2">Create Task</h2>
            <p class="text-slate-500 text-sm">Define your next milestone</p>
        </div>
        
        <div class="space-y-6">
          <!-- Priority Selector -->
          <div>
            <label class="block text-[12px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Urgency</label>
            <div class="flex p-1 bg-slate-50 dark:bg-slate-800/50 rounded-2xl gap-1">
               <button v-for="p in ['low', 'medium', 'high', 'urgent']" :key="p" 
                 @click="newTask.priority = p"
                 :class="[
                   'flex-1 py-3 rounded-xl text-[12px] font-black uppercase tracking-widest transition-all',
                   newTask.priority === p 
                    ? (p === 'urgent' ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/30' : 
                       p === 'high' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30' :
                       p === 'medium' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' :
                       'bg-slate-300 dark:bg-slate-600 text-white')
                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'
                 ]"
               >
                 {{ p }}
               </button>
            </div>
          </div>

          <div>
            <label for="task-title" class="block text-[12px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Task Title</label>
            <input id="task-title" name="title" v-model="newTask.title" type="text" placeholder="What needs to be done?" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-4 outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium">
          </div>

          <!-- Date & Time Row -->
          <div class="grid grid-cols-2 gap-4">
             <div>
                <label for="task-due-date" class="block text-[12px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Due Date</label>
                <input id="task-due-date" name="due_date" v-model="newTask.due_date" type="date" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 outline-none focus:ring-4 focus:ring-indigo-500/20 text-sm font-bold text-slate-600 dark:text-slate-200">
             </div>
             <div>
                <label for="task-due-time" class="block text-[12px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Time (Opt)</label>
                <input id="task-due-time" name="due_time" v-model="newTask.due_time" type="time" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 outline-none focus:ring-4 focus:ring-indigo-500/20 text-sm font-bold text-slate-600 dark:text-slate-200">
             </div>
          </div>

          <div>
            <label for="task-description" class="block text-[12px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Description</label>
            <textarea id="task-description" name="description" v-model="newTask.description" placeholder="Add some context..." class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-4 outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-sm" rows="3"></textarea>
          </div>


          <div class="flex gap-4 pt-4">
            <button @click="showModal = false" class="flex-1 px-6 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl font-bold hover:bg-slate-200 transition-colors">Cancel</button>
            <button @click="saveTask" :disabled="loading" class="flex-1 px-6 py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-xl shadow-indigo-500/30 hover:bg-indigo-700 disabled:opacity-50 transition-all">

              {{ loading ? 'CREATING...' : 'CREATE NOW' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Create Workspace Modal -->
    <div v-if="showCreateWorkspaceModal" class="fixed inset-0 bg-slate-900/90 backdrop-blur-xl flex items-center justify-center p-4 z-[9999]">
      <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl w-full max-w-md p-10 border border-slate-200 dark:border-slate-800 relative overflow-hidden animate-in zoom-in-95 duration-200">
        
        <div class="mb-8 pt-4">
            <h2 class="text-3xl font-black text-slate-800 dark:text-white mb-2">New Context</h2>
            <p class="text-slate-500 text-sm">Create a separate space for your work.</p>
        </div>
        
        <div class="space-y-6">
          <div>
            <label class="block text-[12px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Context Name</label>
            <input v-model="newWorkspace.name" type="text" placeholder="e.g. My Side Hustle" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-4 outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium">
          </div>

          <div>
             <label class="block text-[12px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Type</label>
             <div class="grid grid-cols-3 gap-2">
                <button v-for="type in ['Personal', 'Work', 'Team']" :key="type"
                   @click="newWorkspace.type = type"
                   :class="[
                     'py-3 rounded-xl text-[12px] font-black uppercase tracking-widest transition-all border border-transparent',
                     newWorkspace.type === type 
                       ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' 
                       : 'bg-slate-50 dark:bg-slate-800 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700'
                   ]"
                >
                   {{ type }}
                </button>
             </div>
          </div>

          <div>
            <label class="block text-[12px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Purpose</label>
            <input v-model="newWorkspace.intent" type="text" placeholder="Briefly describe what this is for..." class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-4 outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium text-sm">
          </div>

          <div class="flex gap-4 pt-4">
            <button @click="showCreateWorkspaceModal = false" class="flex-1 px-6 py-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl font-bold hover:bg-slate-200 transition-colors">Cancel</button>
            <button @click="createWorkspace" class="flex-1 px-6 py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-xl shadow-indigo-500/30 hover:bg-indigo-700 transition-all">

              CREATE CONTEXT
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Share Modal -->
    <ShareModal 
      :show="showShareModal" 
      type="Workspace" 
      :id="currentWorkspace?.id" 
      :entity="currentWorkspace" 
      @close="showShareModal = false" 
    />

    <!-- Task Delivery Modal -->
    <TaskDeliveryModal 
      v-if="showDeliveryModal"
      :show="showDeliveryModal"
      :task="deliveringTask"
      @close="showDeliveryModal = false; fetchTasks()"
      @delivered="fetchTasks()"
    />
    <transition name="sheet">
      <div v-if="isMobile && showMobileSheet" class="fixed inset-0 z-[10001] flex items-end">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showMobileSheet = false"></div>
        <div class="relative w-full bg-white dark:bg-slate-900 h-full sm:h-auto sm:rounded-t-[2.5rem] flex flex-col shadow-2xl animate-in slide-in-from-bottom duration-300 overflow-hidden">
           <!-- Mobile Sheet Header -->
           <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-800">
              <button @click="showMobileSheet = false" class="flex items-center gap-2 text-slate-500 font-black uppercase tracking-widest text-xs">
                 <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M15 18l-6-6 6-6"/></svg>
                 Back
              </button>
              <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 dark:text-white">Filters</h3>
              <button @click="resetFilters" class="text-xs font-black uppercase text-indigo-600 px-2">Reset</button>
           </div>
           
           <div class="flex-1 overflow-y-auto p-8 space-y-10">
              <div class="space-y-4">
                 <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">View Mode</p>
                 <div class="grid grid-cols-3 gap-2 bg-slate-50 dark:bg-slate-800 p-1 rounded-xl">
                    <button v-for="v in ['board', 'list', 'calendar']" :key="v"
                      @click="currentView = v"
                      :class="[currentView === v ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500']"
                      class="py-3 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all"
                    >
                       {{ v }}
                    </button>
                 </div>
              </div>

              <div class="grid grid-cols-1 gap-6">
                 <div class="space-y-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Toggles</p>
                    <div class="flex gap-4">
                       <button 
                         @click="workspaceStore.toggleGlobalMode()"
                         class="flex-1 h-14 flex items-center justify-center rounded-2xl transition-colors border"
                         :class="workspaceStore.globalMode ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-slate-100 dark:border-slate-700'"
                       >
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                         <span class="text-xs font-black uppercase tracking-widest">Global View</span>
                       </button>
                       <button 
                         v-if="currentWorkspace && userRole === 'owner' && !workspaceStore.globalMode"
                         @click="showShareModal = true"
                         class="flex-1 h-14 flex items-center justify-center rounded-2xl border bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 border-indigo-100 dark:border-indigo-500/20"
                       >
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
                         <span class="text-xs font-black uppercase tracking-widest">Share</span>
                       </button>
                    </div>
                 </div>

                 <div class="space-y-10">

                        <!-- Context Selection (Mobile) -->
                        <div class="space-y-4">
                           <div class="flex items-center justify-between px-1">
                             <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Context</p>
                             <span v-if="!workspaceStore.globalMode" class="text-[8px] font-black uppercase text-indigo-500 bg-indigo-50 px-2 py-1 rounded">Active Workspace Only</span>
                           </div>
                           <select 
                            v-model="filters.workspaceId" 
                            :disabled="!workspaceStore.globalMode"
                            class="w-full h-14 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 text-sm font-bold shadow-sm outline-none disabled:opacity-50"
                           >
                              <option :value="null">All Contexts</option>
                              <option v-for="ws in workspaceStore.workspaces" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
                           </select>
                        </div>

                        <!-- Priority Selection (Mobile) -->
                        <div class="space-y-4">
                           <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Priority</p>
                           <div class="grid grid-cols-2 gap-3">
                              <button v-for="p in [null, 'low', 'medium', 'high', 'urgent']" :key="p"
                                @click="filters.priority = p"
                                class="h-14 rounded-2xl border text-xs font-black uppercase tracking-widest transition-all"
                                :class="[filters.priority === p ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-500/20' : 'bg-slate-50 dark:bg-slate-800 border-transparent text-slate-500']"
                              >
                                {{ p || 'All' }}
                              </button>
                           </div>
                        </div>

                        <!-- Timeline Presets (Mobile) -->
                        <div class="space-y-4">
                           <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Timeline</p>
                           <div class="grid grid-cols-2 gap-3">
                              <button v-for="opt in rangeOptions" :key="opt.id"
                                @click="filters.timeRange = opt.id; filters.monthYear = ''"
                                class="h-14 rounded-2xl border text-xs font-black uppercase tracking-widest transition-all"
                                :class="[filters.timeRange === opt.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-500/20' : 'bg-slate-50 dark:bg-slate-800 border-transparent text-slate-500']"
                              >
                                {{ opt.label }}
                              </button>
                           </div>
                        </div>

                        <!-- Custom Range (Mobile) -->
                        <div class="space-y-4">
                           <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Custom Dates</p>
                           <div class="grid grid-cols-1 gap-3">
                              <button 
                                @click="filters.timeRange = 'custom'" 
                                class="h-14 rounded-2xl border text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2"
                                :class="[filters.timeRange === 'custom' ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-500/20' : 'bg-slate-50 dark:bg-slate-800 border-transparent text-slate-500']"
                              >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Custom Range
                              </button>
                              
                              <div v-if="filters.timeRange === 'custom'" class="grid grid-cols-2 gap-4 animate-in fade-in duration-300">
                                 <input type="date" v-model="filters.customStart" class="h-14 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 text-xs font-bold shadow-inner">
                                 <input type="date" v-model="filters.customEnd" class="h-14 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 text-xs font-bold shadow-inner">
                              </div>
                           </div>
                        </div>
                     </div>


              </div>
           </div>

           <div class="p-6 border-t border-slate-100 dark:border-slate-800">
              <button 
                @click="showMobileSheet = false" 
                class="w-full h-14 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-indigo-500/20"
              >
                 Apply View
              </button>
           </div>
        </div>
      </div>
    </transition>
    
    <!-- Global View Banner -->
    <div v-if="workspaceStore.globalMode" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900/90 backdrop-blur-md text-white px-8 py-3 rounded-2xl shadow-2xl border border-slate-800 z-[40] flex items-center gap-4 animate-in slide-in-from-bottom-10 duration-500">
       <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
       <p class="text-xs font-black uppercase tracking-[0.2em]">Global Management Mode · All Contexts</p>
       <button @click="workspaceStore.toggleGlobalMode()" class="ml-4 text-[10px] font-black uppercase bg-white/10 hover:bg-white/20 px-3 py-1 rounded-lg transition-all">Exit</button>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useWorkspaceStore } from '../stores/workspace';
import { storeToRefs } from 'pinia';
import TaskDetails from '../components/TaskDetails.vue';
import CalendarView from '../components/CalendarView.vue';
import ContextSwitcher from '../components/ContextSwitcher.vue';
import ShareModal from '../components/ShareModal.vue';

import TaskDeliveryModal from '../components/TaskDeliveryModal.vue';

const auth = useAuthStore();
const workspaceStore = useWorkspaceStore();
const { currentWorkspace } = storeToRefs(workspaceStore);

const columns = [
  { id: 'todo', name: 'Plan', bgClass: 'bg-indigo-500/10', dotClass: 'bg-indigo-500' },
  { id: 'in_progress', name: 'Work', bgClass: 'bg-amber-500/10', dotClass: 'bg-amber-500' },
  { id: 'testing', name: 'Review', bgClass: 'bg-blue-500/10', dotClass: 'bg-blue-500' },
  { id: 'done', name: 'Complete', bgClass: 'bg-emerald-500/10', dotClass: 'bg-emerald-500' }
];

const currentView = ref('board');
const tasks = ref([]);
const selectedTaskId = ref(null);
const showModal = ref(false);
const loading = ref(false);
const draggingOverColumn = ref(null);
const showRangeDropdown = ref(false);
const showShareModal = ref(false);

const showDeliveryModal = ref(false);
const deliveringTask = ref(null);
const showFiltersDrawer = ref(false);

const expandedTasks = ref([]);

// Responsive State
const isMobile = ref(false);
const isTablet = ref(false);
const activeMobileColumnIndex = ref(0);
const showMobileSheet = ref(false);
const touchStartX = ref(0);
const taskTouchStartX = ref(0);


const checkMobile = () => {
  const width = window.innerWidth;
  isMobile.value = width < 768;
  isTablet.value = width >= 768 && width < 1200;
};


const handleTouchStart = (e) => {
  if (!isMobile.value) return;
  touchStartX.value = e.touches[0].clientX;
};

const handleTaskTouchStart = (e) => {
  if (!isMobile.value) return;
  taskTouchStartX.value = e.touches[0].clientX;
};

const handleTaskTouchEnd = async (e, task) => {
  if (!isMobile.value) return;
  const touchEndX = e.changedTouches[0].clientX;
  const diff = taskTouchStartX.value - touchEndX;
  
  if (Math.abs(diff) > 80) {
    if (diff < 0) {
      // Swipe Right -> Complete Task
      await updateTaskStatus(task, 'done');
    } else {
      // Swipe Left -> Open Details
      selectedTaskId.value = task.id;
    }
  }
};

const updateTaskStatus = async (task, newStatus) => {
    try {
        const originalStatus = task.status;
        task.status = newStatus; // Optimistic update
        await axios.patch(`/api/tasks/${task.id}`, { status: newStatus });
        await fetchTasks();
        // Optional: notification or success state
    } catch (error) {
        fetchTasks(); // Revert on failure
        alert('Action failed: ' + (error.response?.data?.message || 'Server error'));
    }
};


const handleTouchEnd = (e) => {
  if (!isMobile.value) return;
  const touchEndX = e.changedTouches[0].clientX;
  const diff = touchStartX.value - touchEndX;
  
  if (Math.abs(diff) > 50) {
    if (diff > 0 && activeMobileColumnIndex.value < columns.length - 1) {
      activeMobileColumnIndex.value++;
    } else if (diff < 0 && activeMobileColumnIndex.value > 0) {
      activeMobileColumnIndex.value--;
    }
  }
};

// Click Outside Directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = function(event) {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event, el);
      }
    };
    document.body.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.body.removeEventListener('click', el.clickOutsideEvent);
  }
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});


const toggleExpand = (id) => {
  if (expandedTasks.value.includes(id)) {
    expandedTasks.value = expandedTasks.value.filter(taskId => taskId !== id);
  } else {
    expandedTasks.value.push(id);
  }
};

const hasExtraDetails = (task) => {
  return task.subtasks?.length > 0 || task.checklists?.length > 0 || task.description;
};

const allSubtasksDone = (task) => {
  if (!task.subtasks?.length) return false;
  return task.subtasks.every(s => s.status === 'done');
};

const userRole = computed(() => {
  if (!currentWorkspace.value || !auth.user) return 'viewer';
  if (currentWorkspace.value.owner_id === auth.user.id) return 'owner';
  const member = currentWorkspace.value.members?.find(m => m.id === auth.user.id);
  return member?.pivot?.role || currentWorkspace.value.pivot?.role || 'viewer';
});

const canDrag = computed(() => !workspaceStore.globalMode);

const subtaskTooltip = (task) => {
  const done = task.subtasks?.filter(s => s.status === 'done').length || 0;
  const total = task.subtasks?.length || 0;
  return `${done}/${total} Sub-tasks Completed`;
};

const checklistProgress = (task) => {
  if (!task.checklists?.length) return 0;
  const done = task.checklists.filter(c => c.is_completed).length;
  return (done / task.checklists.length) * 100;
};

const getRoleForTask = (task) => {
  if (!task || !auth.user) return 'viewer';
  const workspace = task.board?.plan?.workspace;
  if (!workspace) return 'viewer';
  
  // Find the workspace in store to get the user's role
  const myWorkspace = workspaceStore.workspaces.find(w => w.id === workspace.id);
  if (myWorkspace) {
    if (myWorkspace.owner_id === auth.user.id) return 'owner';
    return myWorkspace.pivot?.role || 'viewer';
  }
  
  // Fallback check (owner)
  if (workspace.owner_id === auth.user.id) return 'owner';
  
  return 'viewer';
};

const canDeleteTask = (task) => {
  if (!task || !auth.user) return false;
  const isCreator = task.created_by === auth.user.id;
  
  const role = getRoleForTask(task);
  return isCreator || role === 'owner' || role === 'admin';
};

const confirmDelete = async (task) => {
  if (confirm('Are you sure you want to delete this task?')) {
    try {
      await axios.delete(`/api/tasks/${task.id}`);
      fetchTasks();
    } catch (error) {
      alert('Delete failed');
    }
  }
};



const rangeOptions = [
  { id: 'all', label: 'All Time' },
  { id: 'today', label: 'Today' },
  { id: 'last_7_days', label: 'Last 7 Days' },
  { id: 'last_30_days', label: 'Last 30 Days' },
  { id: 'last_360_days', label: 'Last 360 Days' }
];


const activeRangeLabel = computed(() => {
  if (filters.value.monthYear) {
    const [year, month] = filters.value.monthYear.split('-');
    const date = new Date(year, month - 1);
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  }
  if (filters.value.timeRange === 'custom') return 'Custom';
  return rangeOptions.find(o => o.id === filters.value.timeRange)?.label || 'Select Range';
});

const resetNewTask = () => {
  newTask.value = {
    title: '',
    description: '',
    priority: 'medium',
    status: 'todo',
    due_date: '',
    due_time: '',
    assigned_to: null
  };
};

const newTask = ref({
  title: '',
  description: '',
  priority: 'medium',
  status: 'todo',
  due_date: '',
  due_time: '',
  assigned_to: null
});

const suggestion = ref(null);

watch(() => newTask.value.due_date, (newDate) => {
  if (!newDate) {
    suggestion.value = null;
    return;
  }
  
  const now = new Date();
  const due = new Date(newDate);
  const diffHours = (due - now) / (1000 * 60 * 60);

  if (diffHours > 0 && diffHours < 24) {
    suggestion.value = { message: 'This is due soon. Set to High priority?', priority: 'high' };
  } else if (diffHours > 0 && diffHours < 72) {
    suggestion.value = { message: 'Short deadline. Set to Medium priority?', priority: 'medium' };
  } else {
    suggestion.value = null;
  }
});

const applySuggestion = () => {
  if (suggestion.value) {
    newTask.value.priority = suggestion.value.priority;
    suggestion.value = null;
  }
};
const getWorkspaceIcon = (type) => {
  switch(type) {
    case 'Personal': return '🧍';
    case 'Work': return '💼';
    case 'Company': return '🏢';
    case 'Team': return '👥';
    default: return '📁';
  }
};

const filters = ref({
  priority: null,
  monthYear: '',
  timeRange: 'last_7_days', // Default as requested
  customStart: '',
  customEnd: '',
  workspaceId: null
});


const hasActiveFilters = computed(() => {
  return filters.value.priority !== null || filters.value.timeRange !== 'all' || filters.value.workspaceId !== null || !!filters.value.monthYear;
});

const resetFilters = () => {
  filters.value = { priority: null, monthYear: null, timeRange: 'last_7_days', workspaceId: null };
};


const filteredTasks = computed(() => {
  return tasks.value.filter(task => {
    // Workspace filter
    if (filters.value.workspaceId && task.board?.plan?.workspace_id !== filters.value.workspaceId) return false;

    // Priority filter
    if (filters.value.priority && task.priority !== filters.value.priority) return false;
    
    // Time Range filter
    const now = new Date();
    const taskDate = new Date(task.deadline || task.created_at);
    
    if (filters.value.timeRange !== 'all') {
       if (filters.value.timeRange === 'today') {
          const today = new Date().toISOString().slice(0, 10);
          const tDate = (task.deadline || task.created_at).slice(0, 10);
          if (tDate !== today) return false;
       } else if (filters.value.timeRange === 'last_7_days') {
          const limit = new Date(new Date().setDate(now.getDate() - 7));
          if (taskDate < limit || taskDate > now) return false;
       } else if (filters.value.timeRange === 'last_14_days') {
          const limit = new Date(new Date().setDate(now.getDate() - 14));
          if (taskDate < limit || taskDate > now) return false;
       } else if (filters.value.timeRange === 'last_30_days') {
          const limit = new Date(new Date().setDate(now.getDate() - 30));
          if (taskDate < limit || taskDate > now) return false;
       } else if (filters.value.timeRange === 'last_360_days') {
          const limit = new Date(new Date().setDate(now.getDate() - 360));
          if (taskDate < limit || taskDate > now) return false;
       } else if (filters.value.timeRange === 'custom') {
          if (filters.value.customStart) {
             if (taskDate < new Date(filters.value.customStart)) return false;
          }
          if (filters.value.customEnd) {
             const end = new Date(filters.value.customEnd);
             end.setHours(23, 59, 59);
             if (taskDate > end) return false;
          }
       }
    }

    if (filters.value.monthYear) {
       const [year, month] = filters.value.monthYear.split('-');
       if (taskDate.getFullYear() !== parseInt(year) || (taskDate.getMonth() + 1) !== parseInt(month)) return false;
    }


    return true;
  });
});



const openModal = () => {
  showModal.value = true;
};

const fetchTasks = async () => {
  const wsId = workspaceStore.globalMode ? 'all' : currentWorkspace.value?.id;
  if (!wsId && !workspaceStore.globalMode) return;
  
  try {
    const response = await axios.get(`/api/tasks`, {
        params: { workspace_id: wsId }
    });
    tasks.value = Array.isArray(response.data) ? response.data : [];
  } catch (error) {
    console.error('Error fetching tasks:', error);
    tasks.value = [];
  }
};

// Reload tasks when workspace or global mode changes
watch(currentWorkspace, () => {
  if (!workspaceStore.globalMode) {
    fetchTasks();
  }
});

watch(() => workspaceStore.globalMode, (newVal) => {
  if (newVal) {
    // When entering Global Mode, clear the workspace filter so we see EVERYTHING
    filters.value.workspaceId = null;
  } else if (currentWorkspace.value) {
    // When exiting, lock back to current workspace
    filters.value.workspaceId = currentWorkspace.value.id;
  }
  fetchTasks();
}, { immediate: true });

// Sync filter if workspace changes in single mode
watch(currentWorkspace, (newWs) => {
  if (!workspaceStore.globalMode && newWs) {
    filters.value.workspaceId = newWs.id;
  }
});


const saveTask = async () => {
    if (!newTask.value.title) return;
    loading.value = true;
    try {
        const taskData = { ...newTask.value };
        if (taskData.due_date) {
            const time = taskData.due_time || '23:59';
            taskData.deadline = `${taskData.due_date} ${time}:00`;
        }

        // Add context to payload
        if (currentWorkspace.value) {
            taskData.workspace_id = currentWorkspace.value.id;
            
            // Hierarchy: Workspace -> Plan -> Board -> Task
            const firstPlan = currentWorkspace.value.plans?.[0];
            const firstBoard = firstPlan?.boards?.[0];

            if (firstBoard) {
                taskData.board_id = firstBoard.id;
            } else {
                alert('No boards found in this workspace. Please create a plan and board first.');
                loading.value = false;
                return;
            }
        }

        let deadline = null;
        if (newTask.value.due_date) {
            const time = newTask.value.due_time || '23:59';
            deadline = `${newTask.value.due_date} ${time}:00`;
        }

        await axios.post('/api/tasks', { ...taskData, deadline });
        showModal.value = false;
        resetNewTask();
        await fetchTasks();
  } catch (error) {
    alert('Error: ' + (error.response?.data?.message || 'Connection failed'));
  } finally {
    loading.value = false;
  }
};

// Create Workspace Logic
const showCreateWorkspaceModal = ref(false);
const newWorkspace = ref({
    name: '',
    type: 'Personal',
    intent: 'Personal Use'
});

const openCreateModal = () => {
    showCreateWorkspaceModal.value = true;
};

const createWorkspace = async () => {
    if (!newWorkspace.value.name) return;
    try {
        await workspaceStore.createWorkspace({
            name: newWorkspace.value.name,
            type: newWorkspace.value.type,
            intent: newWorkspace.value.intent
        });
        showCreateWorkspaceModal.value = false;
        newWorkspace.value = { name: '', type: 'Personal', intent: 'Personal Use' };
        // Refetch workspaces (which now includes their projects)
        await workspaceStore.fetchWorkspaces(); 
    } catch (error) {
        const message = error.response?.data?.message || 'Failed to create workspace';
        alert(message);
    }
};

const formatDeadline = (dl) => {
   const date = new Date(dl);
   const now = new Date();
   const isToday = date.toDateString() === now.toDateString();
   const hasTime = date.getHours() !== 23 || date.getMinutes() !== 59;
   
   const datePart = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
   const timePart = hasTime ? date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';
   
   if (isToday) return `Today ${timePart}`.trim();
   return `${datePart} ${timePart}`.trim();
};

const onDragStart = (event, task) => {
  event.dataTransfer.dropEffect = 'move';
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.setData('taskId', task.id);
  event.target.classList.add('dragging');
};

const onDrop = async (event, newStatus) => {
  draggingOverColumn.value = null;
  if (!canDrag.value) return;

  const taskId = event.dataTransfer.getData('taskId');
  const task = tasks.value.find(t => t.id == taskId);
  
  if (task) {
    // If dropped in the same column, ignore (no-op)
    if (task.status === newStatus) return;

    let updateData = { status: newStatus };

    // Auto-assignment logic: only reassign when moving to 'Work' status (in_progress)
    if (newStatus === 'in_progress' && task.assigned_to !== auth.user.id) {
      updateData.assigned_to = auth.user.id;
    }

    const oldStatus = task.status;
    const oldAssignee = task.assigned_to;
    
    // Optimistic UI update
    task.status = newStatus;

    try {
      await axios.patch(`/api/tasks/${taskId}`, updateData);
      
      // If moved to done, prompt for delivery
      if (newStatus === 'done' && !task.delivery) {
          deliveringTask.value = task;
          showDeliveryModal.value = true;
      }

      fetchTasks();
    } catch (error) {
      task.status = oldStatus;
      task.assigned_to = oldAssignee;
      alert('Failed to sync. ' + (error.response?.data?.message || 'Server error'));
    }
  }
};

const priorityClass = (p) => {
  switch (p) {
    case 'urgent': return 'bg-rose-500 text-white shadow-lg shadow-rose-500/30';
    case 'high': return 'bg-orange-500 text-white shadow-lg shadow-orange-500/30';
    case 'medium': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400';
    default: return 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400';
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'No date';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

onMounted(async () => {
  await workspaceStore.fetchWorkspaces();
  // If currentWorkspace was already set (from cache) and fetchWorkspaces didn't change it, 
  // we might need to fetch tasks manually. But fetchWorkspaces always resets currentWorkspace
  // if it finds a better match or for the first time.
  if (tasks.value.length === 0) {
    fetchTasks();
  }
});
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
.dragging { opacity: 0.5; transform: scale(0.95); }
[draggable="true"] { transition: all 0.2s ease-out; }

/* Suggestion Banner Animation */
.fade-enter-active, .fade-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.fade-enter-from, .fade-leave-to { transform: translateY(-100%); opacity: 0; }

/* Expand Transition */
.expand-enter-active, .expand-leave-active { 
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
  max-height: 500px;
  overflow: hidden;
}
.expand-enter-from, .expand-leave-to { 
  max-height: 0;
  opacity: 0;
  transform: translateY(-10px);
}

input[type="date"], input[type="time"], input[type="month"] {
  appearance: none;
  -webkit-appearance: none;
}
</style>
