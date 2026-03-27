<template>
  <transition name="fade">
    <div class="fixed inset-0 z-[10000] flex justify-end overflow-hidden">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
      
      <!-- Drawer -->
      <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 h-full shadow-2xl flex flex-col z-10 drawer-content transition-all duration-300">
        
        <!-- Status Banner if Completed (Loading Safe) -->
        <div v-if="task?.status === 'done'" class="bg-[var(--color-status-done)] text-white px-6 py-2 flex items-center gap-2 justify-center text-xs font-heading font-bold uppercase tracking-[0.3em] relative overflow-hidden group animate-in slide-in-from-top duration-300">
          {{ task.deliveries?.length > 0 ? 'Task Delivered' : 'Task Completed' }}
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-4">
            <!-- Back button for mobile -->
            <button @click="$emit('close')" class="sm:hidden flex items-center gap-2 text-slate-500 font-heading font-bold uppercase tracking-widest text-xs pr-2 border-r border-slate-100 dark:border-slate-800">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M15 18l-6-6 6-6"/></svg>
              Back
            </button>

            <!-- Custom Priority Dropdown -->
            <div class="relative priority-dropdown">
              <button 
                @click.stop="togglePriorityMenu"
                :disabled="!canEditFull"
                :class="[
                  priorityClass(editedTask.priority), 
                  {'cursor-not-allowed opacity-50': !canEditFull},
                  {'animate-pulse-subtle': isSyncing}
                ]" 
                class="flex items-center gap-1.5 text-[10px] font-heading font-extrabold uppercase px-3 py-1.5 rounded-full tracking-widest border-none outline-none cursor-pointer transition-all active-tap relative"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50"></span>
                {{ editedTask.priority }}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 opacity-50 ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m6 9 6 6 6-6"/></svg>
                
                <!-- Tiny sync indicator -->
                <div v-if="isSyncing" class="absolute -top-1 -right-1 w-2 h-2 bg-indigo-500 rounded-full border border-white dark:border-slate-800 animate-ping"></div>
              </button>

              <transition name="scale-in">
                <div v-if="showPriorityMenu" class="absolute top-full left-0 mt-2 w-40 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden z-50 p-1.5 origin-top-left animate-in fade-in zoom-in-95 duration-200">
                   <div class="space-y-1">
                      <button 
                        v-for="p in ['low', 'medium', 'high', 'urgent']" 
                        :key="p"
                        @click="selectPriority(p)"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors group"
                      >
                         <div class="flex items-center gap-2">
                            <div :class="priorityDotClass(p)" class="w-2 h-2 rounded-full ring-1 ring-offset-1 ring-offset-white dark:ring-offset-slate-800"></div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white">{{ p }}</span>
                         </div>
                         <svg v-if="editedTask.priority === p" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                      </button>
                   </div>
                </div>
              </transition>
            </div>

            <!-- Custom Status Dropdown -->
            <div class="relative">
              <button 
                @click.stop="toggleStatusMenu"
                :disabled="!canChangeStatus"
                class="flex items-center gap-2 text-[10px] font-heading font-extrabold uppercase px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg tracking-widest border border-slate-200 dark:border-slate-700 cursor-pointer transition-pop active-tap hover:bg-slate-200 dark:hover:bg-slate-700"
                :class="{'cursor-not-allowed opacity-80': !canChangeStatus}"
              >
                <span class="text-xs">{{ getStatusIcon(editedTask.status) }}</span>
                {{ getStatusLabel(editedTask.status) }}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m6 9 6 6 6-6"/></svg>
              </button>

              <transition name="scale-in">
                <div v-if="showStatusMenu" class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden z-50 p-1.5 origin-top-left animate-in fade-in zoom-in-95 duration-200">
                   <div class="space-y-1">
                      <template v-for="option in statusOptions" :key="option.value">
                        <button 
                          @click="selectStatus(option.value)"
                          class="w-full flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors group text-left"
                        >
                           <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 dark:bg-slate-900 text-slate-500 group-hover:scale-110 transition-transform">
                              <span class="text-sm">{{ option.icon }}</span>
                           </div>
                           <div class="flex-1">
                              <p class="text-xs font-bold uppercase tracking-widest text-slate-700 dark:text-slate-200">{{ option.label }}</p>
                              <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ option.desc }}</p>
                           </div>
                           <div v-if="editedTask.status === option.value" class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                        </button>
                      </template>
                   </div>
                </div>
              </transition>
            </div>

            <div v-if="editedTask.status === 'testing'" class="hidden sm:flex items-center gap-2 px-3 py-1 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800 ml-2 transition-all">
               <input 
                 type="checkbox" 
                 :id="'review-check-' + taskId"
                 :name="'is_reviewed_' + taskId"
                 v-model="editedTask.is_reviewed" 
                 @change="updateTask" 
                 class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer"
               >
               <label :for="'review-check-' + taskId" class="text-[10px] font-bold uppercase tracking-widest text-blue-700 dark:text-blue-300 cursor-pointer select-none">Mark Reviewed</label>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <div class="hidden sm:flex items-center gap-2 mr-4" v-if="task">
               <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M8 7h6"/><path d="M8 11h8"/></svg>
               <p class="text-[10px] font-heading font-bold text-slate-400 uppercase tracking-[0.2em]">TASK-{{ task.id }}</p>
            </div>
            <transition name="fade">
              <div v-if="isSyncing" class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl text-[8px] font-black uppercase tracking-widest animate-in fade-in slide-in-from-right-2 duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                Syncing
              </div>
            </transition>

            <button v-if="canDelete && task" @click="deleteTask" class="p-2 hover:bg-rose-50 dark:hover:bg-rose-500/10 text-slate-300 hover:text-rose-500 rounded-xl transition-all active-tap" title="Delete Task">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </button>
            <button @click="$emit('close')" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors active-tap">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
          <!-- Skeleton Loader -->
          <TaskDetailsSkeleton v-if="!task && isLoading" />

          <!-- Error State -->
          <div v-else-if="fetchError" class="flex flex-col items-center justify-center py-20 text-center space-y-4 animate-in fade-in duration-300">
            <div class="p-4 bg-rose-50 dark:bg-rose-500/10 rounded-2xl">
               <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-widest">{{ fetchError }}</p>
              <p class="text-xs text-slate-400 mt-1">Please check your connection and try again.</p>
            </div>
            <button @click="fetchTask" class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-500/20 active-tap">Retry Connection</button>
          </div>

          <!-- Actual Content -->
          <div v-else-if="task" class="space-y-10 animate-in fade-in duration-300">

            <!-- Title & Description -->
            <section>
              <label :for="'task-title-' + taskId" class="sr-only">Task Title</label>
              <input 
                :id="'task-title-' + taskId"
                name="title"
                v-model="editedTask.title" 
                @blur="debouncedUpdateTask"
                :disabled="!canEditFull"
                class="w-full text-3xl font-heading font-bold text-slate-900 dark:text-white bg-transparent border-none outline-none focus:ring-0 mb-4 p-0"
                :class="{'cursor-not-allowed': !canEditFull}"
                placeholder="Task Title"
              >
              <label :for="'task-desc-' + taskId" class="sr-only">Task Description</label>
              <textarea 
                :id="'task-desc-' + taskId"
                name="description"
                v-model="editedTask.description" 
                @blur="debouncedUpdateTask"
                :disabled="!canEditFull"
                class="w-full text-base text-slate-500 dark:text-slate-400 bg-transparent border-none outline-none focus:ring-0 p-0 resize-none min-h-[100px]"
                :class="{'cursor-not-allowed': !canEditFull}"
                placeholder="Add a detailed description..."
              ></textarea>
            </section>

            <!-- Delivery Section -->
            <DeliveriesSection 
              v-if="task"
              :items="allAttachments"
              :can-add="canEditFull"
              @add="handleNewAttachments"
              @remove="deleteDelivery"
              @retry="retryUpload"
            />

            <section class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-6 border-y border-slate-50 dark:border-slate-800/50">
              <div class="space-y-4">
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <label :for="'start-date-' + taskId" class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Start Date & Time</label>
                    <transition name="fade">
                      <span v-if="calendarStatus" class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 rounded flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        {{ calendarStatus }}
                      </span>
                    </transition>
                  </div>
                  <div class="flex gap-2">
                    <CustomDatePicker :id="'start-date-' + taskId" :name="'start_date_' + taskId" v-model="editedTask.start_date" @change="updateTask" :disabled="!canEditFull" />
                    <input 
                      :id="'start-time-' + taskId"
                      name="start_time"
                      type="time" 
                      v-model="editedTask.start_time" 
                      @change="updateTask"
                      :disabled="!canEditFull"
                      class="w-24 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl px-4 py-3 text-base font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-indigo-500/20 active-tap"
                      :class="{'cursor-not-allowed opacity-80': !canEditFull}"
                    >
                  </div>
                </div>
                <div v-if="workspaceMembers.length > 1" class="space-y-2">
                  <label :for="'assignee-' + taskId" class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Assignee</label>
                  <select 
                    :id="'assignee-' + taskId"
                    name="assigned_to"
                    v-model="editedTask.assigned_to" 
                    @change="updateTask"
                    :disabled="!canEditFull"
                    class="w-full bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl px-4 py-3 text-base font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-indigo-500/20 appearance-none cursor-pointer transition-pop active-tap"
                    :class="{'cursor-not-allowed opacity-80': !canEditFull}"
                  >
                    <option :value="null">Unassigned</option>
                    <option v-for="member in workspaceMembers" :key="member.id" :value="member.id">
                      {{ member.display_name }}
                    </option>
                  </select>
                </div>

                <!-- Visibility Toggle -->
                <div class="space-y-2">
                   <label class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Visibility</label>
                   <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800/50">
                      <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ editedTask.is_public ? 'Public Team Task' : 'Private Task' }}</span>
                      <button 
                        @click="editedTask.is_public = !editedTask.is_public; updateTask()"
                        :disabled="!canEditFull"
                        :class="[
                          'w-11 h-6 rounded-full transition-all relative',
                          editedTask.is_public ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600',
                          {'opacity-50 cursor-not-allowed': !canEditFull}
                        ]"
                      >
                         <div :class="[
                           'absolute top-1 w-4 h-4 bg-white rounded-full transition-all shadow-sm',
                           editedTask.is_public ? 'left-6' : 'left-1'
                         ]"></div>
                      </button>
                   </div>
                </div>
              </div>
              <div class="space-y-2">
                <label :for="'due-date-' + taskId" class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Due Date</label>
                <div class="flex gap-2">
                  <CustomDatePicker :id="'due-date-' + taskId" :name="'due_date_' + taskId" v-model="editedTask.due_date" @change="updateTask" :disabled="!canEditFull" />
                  <input 
                    :id="'due-time-' + taskId"
                    name="due_time"
                    type="time" 
                    v-model="editedTask.due_time" 
                    @change="updateTask"
                    class="w-24 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl px-4 py-3 text-base font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-indigo-500/20 active-tap"
                  >
                </div>
              </div>
            </section>

            <!-- Sub-tasks -->
            <section class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <h3 class="text-sm font-heading font-bold uppercase tracking-[0.2em] text-slate-400">Sub-tasks</h3>
                  
                  <!-- Progress Bar -->
                  <div v-if="task.subtasks?.length" class="flex items-center gap-2">
                     <div class="h-1.5 w-24 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 transition-all duration-500 ease-out" :style="{ width: subtaskProgress + '%' }"></div>
                     </div>
                     <span class="text-[9px] font-black text-slate-400">{{ Math.round(subtaskProgress) }}%</span>
                  </div>
                </div>
              </div>
              
              <div class="space-y-1">
                <!-- Existing Subtasks -->
                <transition-group name="list">
                  <div v-for="sub in task.subtasks" :key="sub.id" class="group flex items-center gap-3 p-2 -mx-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-xl transition-all">
                      <!-- Checkbox -->
                      <button 
                        @click.stop="toggleSubtask(sub)" 
                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                        :class="sub.status === 'done' ? 'bg-indigo-500 border-indigo-500' : 'border-slate-300 dark:border-slate-600 hover:border-indigo-500'"
                      >
                        <svg v-if="sub.status === 'done'" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"/></svg>
                      </button>

                      <!-- Inline Edit Title -->
                      <label :for="'subtask-title-' + sub.id" class="sr-only">Subtask Title</label>
                      <input 
                        :id="'subtask-title-' + sub.id"
                        :name="'subtask_title_' + sub.id"
                        v-model="sub.title" 
                        @blur="updateSubtaskTitle(sub)"
                        @keydown.enter="$event.target.blur()"
                        type="text"
                        class="flex-1 bg-transparent border-none outline-none text-sm font-medium text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:text-indigo-600 transition-colors"
                        :class="{'line-through text-slate-400': sub.status === 'done'}"
                      >

                      <!-- Assignee or Spinner -->
                      <div class="flex items-center gap-2">
                          <div v-if="sub.is_optimistic" class="w-4 h-4 rounded-full border-2 border-slate-200 border-t-indigo-500 animate-spin"></div>
                          <div v-else class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-2">
                            <img 
                              v-if="sub.assigned_to" 
                              :src="sub.assignee?.avatar || 'https://ui-avatars.com/api/?name=' + sub.assignee?.display_name" 
                              class="w-5 h-5 rounded-full"
                              title="Assigned"
                            >
                            <button @click="deleteSubtaskId(sub.id)" class="p-1.5 text-slate-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-colors">
                              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                            </button>
                          </div>
                      </div>
                  </div>
                </transition-group>

                <!-- Add New Input (Always Visible) -->
                <div class="flex items-center gap-3 p-2 -mx-2 opacity-50 focus-within:opacity-100 transition-opacity">
                   <div class="w-5 h-5 rounded-full border-2 border-slate-200 dark:border-slate-700 border-dashed shrink-0 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                   </div>
                   <input 
                       :id="'new-subtask-' + taskId"
                       name="new_subtask"
                       ref="newSubtaskInputRef"
                       v-model="newSubtaskTitle" 
                       @keydown.enter="createSubtask"
                       :disabled="isCreatingSubtask"
                       type="text"
                       placeholder="Add sub-task..." 
                       class="flex-1 bg-transparent border-none outline-none text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 focus:placeholder-slate-300 disabled:opacity-50"
                   />
                   <div v-if="newSubtaskTitle" class="text-[10px] font-bold text-slate-300 uppercase tracking-wider animate-in fade-in">Press Enter</div>
                </div>
              </div>
            </section>

            <!-- Activity Timeline -->
            <section class="space-y-6 pt-6 border-t border-slate-100 dark:border-slate-800">
              <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-400">Activity Log</h3>
              <div class="space-y-6">
                <div v-for="log in task.activity_logs" :key="log.id" class="flex gap-4">
                   <img :src="log.user?.avatar || 'https://ui-avatars.com/api/?name=' + log.user?.display_name" class="w-8 h-8 rounded-full shrink-0 mt-1">
                    <div class="space-y-1">
                      <p class="text-sm">
                        <span class="font-bold text-slate-800 dark:text-white">{{ log.user?.display_name || 'System' }}</span>
                        <span class="text-slate-500 ml-1">did {{ formatAction(log) }}</span>
                      </p>
                      <p class="text-[12px] text-slate-400 font-bold tracking-widest">{{ formatDate(log.created_at) }}</p>
                    </div>
                </div>
              </div>
            </section>
          </div>
        </div>

        <!-- Footer Info (Loading Safe) -->
        <div v-if="task" class="p-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-3 gap-6 animate-in fade-in slide-in-from-bottom duration-300">
          <div class="flex items-center gap-3">
            <img :src="task.creator?.avatar || 'https://ui-avatars.com/api/?name=' + (task.creator?.display_name || 'System')" class="w-8 h-8 rounded-full ring-2 ring-white dark:ring-slate-800 object-cover">
             <div class="text-left">
              <p class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Owner</p>
              <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ task.creator?.display_name }}</p>
            </div>
          </div>
          <div class="text-right sm:text-left flex flex-col justify-center">
              <p class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Recipient</p>
              <p class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate">{{ task.assignee?.display_name || 'Unassigned' }}</p>
           </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue';
import axios from 'axios';
import DeliveriesSection from './DeliveriesSection.vue';
import CustomDatePicker from './CustomDatePicker.vue';
import TaskDetailsSkeleton from './TaskDetailsSkeleton.vue';
import { useAuthStore } from '../stores/auth';
import { useUIStore } from '../stores/ui';
import { compressImage } from '../utils/imageCompressor';

const auth = useAuthStore();
const ui = useUIStore();
const props = defineProps({
  taskId: { type: [Number, String], required: true },
  role: { type: String, default: 'viewer' },
  initialData: { type: Object, default: null }
});

const emit = defineEmits(['close', 'updated']);

const canEditFull = computed(() => {
  if (!auth.user || !task.value) return false;
  return props.role === 'owner' || props.role === 'editor' || task.value.created_by === auth.user.id;
});
const canChangeStatus = computed(() => true);
const canDelete = computed(() => props.role === 'owner');

const task = ref(null);
const isLoading = ref(false);
const fetchError = ref(null);
const workspaceMembers = ref([]);
const debounceTimer = ref(null);

const debouncedUpdateTask = () => {
  clearTimeout(debounceTimer.value);
  debounceTimer.value = setTimeout(() => updateTask(), 800);
};
const isSyncing = ref(false);
const calendarStatus = ref(null);
const syncTimeout = ref(null);
const showPriorityMenu = ref(false);
const showStatusMenu = ref(false);
const pendingDeliveries = ref([]);

const allAttachments = computed(() => {
  return [...pendingDeliveries.value, ...flattenedDeliveries.value];
});

const togglePriorityMenu = () => { showPriorityMenu.value = !showPriorityMenu.value; showStatusMenu.value = false; };
const toggleStatusMenu = () => { showStatusMenu.value = !showStatusMenu.value; showPriorityMenu.value = false; };

const closeMenus = (e) => {
   if (!e.target.closest('.priority-dropdown') && !e.target.closest('.status-dropdown')) {
      showPriorityMenu.value = false;
      showStatusMenu.value = false;
   }
};

onMounted(() => {
    if (props.initialData) {
       task.value = props.initialData;
       syncLocalStateFromTask();
    }
    fetchTask();
    window.addEventListener('click', closeMenus);
});

watch(() => props.taskId, (newId) => {
   if (newId) {
      if (props.initialData) {
         task.value = props.initialData;
         syncLocalStateFromTask();
      } else {
         task.value = null; // Show skeleton if no initial data
      }
      fetchTask();
   }
});

onUnmounted(() => {
   window.removeEventListener('click', closeMenus);
});

const selectPriority = (p) => {
   if (editedTask.value.priority === p) {
      showPriorityMenu.value = false;
      return;
   }
   
   // Close instantly for SaaS feel
   showPriorityMenu.value = false;
   
   editedTask.value.priority = p;
   updateTask();
};

const selectStatus = (s) => {
   editedTask.value.status = s;
   updateTask();
   showStatusMenu.value = false;
};

const priorityDotClass = (p) => {
  switch (p) {
    case 'urgent': return 'bg-rose-500 ring-rose-500';
    case 'high': return 'bg-orange-500 ring-orange-500';
    case 'medium': return 'bg-indigo-500 ring-indigo-500';
    default: return 'bg-slate-400 ring-slate-400';
  }
};

const getStatusIcon = (s) => {
   switch(s) {
      case 'todo': return '📅';
      case 'in_progress': return '⚡';
      case 'testing': return '👀';
      case 'done': return '✅';
      default: return '⚪';
   }
};

const getStatusLabel = (s) => {
   switch(s) {
      case 'todo': return 'Plan';
      case 'in_progress': return 'Work';
      case 'testing': return 'Review';
      case 'done': return 'Complete';
      default: return s;
   }
};

const statusOptions = computed(() => {
   const opts = [
      { value: 'todo', label: 'Plan', icon: '📅', desc: 'Not started' },
      { value: 'in_progress', label: 'Work', icon: '⚡', desc: 'Active execution' },
   ];
   
   if (task.value?.board?.plan?.workspace?.settings?.enable_review_step !== false) {
      opts.push({ value: 'testing', label: 'Review', icon: '👀', desc: 'Quality control' });
   }
   
   opts.push({ value: 'done', label: 'Complete', icon: '✅', desc: 'Finished' });
   return opts;
});

const priorityClass = (p) => {
  switch (p) {
    case 'urgent': return 'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400 ring-1 ring-rose-500/20';
    case 'high': return 'bg-orange-500/10 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400 ring-1 ring-orange-500/20';
    case 'medium': return 'bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 ring-1 ring-indigo-500/20';
    default: return 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 ring-1 ring-slate-200 dark:ring-slate-700';
  }
};
const showDeliveryModal = ref(false);
const showAddSubtask = ref(false);
const newSubtaskTitle = ref('');
const newSubtaskInputRef = ref(null);

const subtaskProgress = computed(() => {
  if (!task.value?.subtasks?.length) return 0;
  const done = task.value.subtasks.filter(s => s.status === 'done').length;
  return (done / task.value.subtasks.length) * 100;
});

const flattenedDeliveries = computed(() => {
  if (!task.value?.deliveries) return [];
  const items = [];
  task.value.deliveries.forEach(d => {
    d.items.forEach(i => {
      items.push({
        ...i,
        delivery_id: d.id, 
        id: i.id, // Actual item ID
        creator: d.user?.display_name,
        created_at: d.created_at
      });
    });
  });
  return items;
});

const handleNewAttachments = async (newItems) => {
  for (const item of newItems) {
    if (item.status === 'syncing') {
      pendingDeliveries.value.push(item);
      uploadAndDeliver(item);
    } else {
      // Link or other synchronous item
      try {
        isSyncing.value = true;
        await axios.post(`/api/tasks/${props.taskId}/deliver`, {
          items: [item],
          notes: ''
        });
        await fetchTask();
        emit('updated');
      } catch (e) {
        ui.notify('Failed to add attachment', 'error');
      } finally {
        isSyncing.value = false;
      }
    }
  }
};

const uploadAndDeliver = async (item) => {
  try {
    let fileToUpload = item.file;
    
    // 1. Browser-based compression
    if (item.type === 'image') {
      try {
        const compressed = await compressImage(item.file, { quality: 0.8 });
        if (compressed) fileToUpload = compressed;
      } catch (e) {
        console.warn('Compression failed, using original', e);
      }
    }

    // 2. Upload file
    const formData = new FormData();
    formData.append('file', fileToUpload);
    const uploadRes = await axios.post('/api/delivery-upload', formData);

    // 3. Create Delivery Record
    await axios.post(`/api/tasks/${props.taskId}/deliver`, {
      items: [{
        type: item.type,
        name: item.name,
        content: uploadRes.data.path,
        description: null
      }],
      notes: ''
    });

    // 4. Success: Cleanup and refresh
    pendingDeliveries.value = pendingDeliveries.value.filter(p => p.id !== item.id);
    await fetchTask();
    emit('updated');
  } catch (e) {
    console.error('Upload/Delivery failed', e);
    const index = pendingDeliveries.value.findIndex(p => p.id === item.id);
    if (index !== -1) {
      pendingDeliveries.value[index].status = 'error';
    }
    ui.notify(`Failed to upload ${item.name}`, 'error');
  }
};

const retryUpload = (item) => {
  const index = pendingDeliveries.value.findIndex(p => p.id === item.id);
  if (index !== -1) {
    pendingDeliveries.value[index].status = 'syncing';
    uploadAndDeliver(pendingDeliveries.value[index]);
  }
};

const deleteDelivery = async (deliveryId) => {
  const confirmed = await ui.confirm('Remove this attachment?', { variant: 'rose', confirmText: 'Remove' });
  if (!confirmed) return;
  try {
    isSyncing.value = true;
    await axios.delete(`/api/deliveries/${deliveryId}`);
    await fetchTask();
    emit('updated');
    ui.notify('Attachment removed', 'success');
  } catch (e) {
    ui.notify('Failed to remove attachment', 'error');
  } finally {
    isSyncing.value = false;
  }
};

const editedTask = ref({ title: '', description: '', priority: '', status: '', due_date: '', due_time: '', start_date: '', start_time: '', assigned_to: null, is_reviewed: false, is_public: true });

const fetchTask = async () => {
  if (!props.taskId) return;
  
  isLoading.value = true;
  fetchError.value = null;
  try {
    const response = await axios.get(`/api/tasks/${props.taskId}`);
    task.value = response.data;
    
    syncLocalStateFromTask();

    fetchWorkspaceMembers(response.data.board.plan.workspace_id);
  } catch (error) {
    console.error('Error fetching task details', error);
    fetchError.value = 'Failed to load task details';
  } finally {
    isLoading.value = false;
  }
};

const syncLocalStateFromTask = () => {
    if (!task.value) return;

    const formatDateForInput = (dateStr) => {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      const d = String(date.getDate()).padStart(2, '0');
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const y = date.getFullYear();
      return `${d}/${m}/${y}`;
    };
    
    const formatTimeForInput = (dateStr) => {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0');
    };

    editedTask.value = { 
      title: task.value.title, 
      description: task.value.description,
      priority: task.value.priority,
      status: task.value.status,
      due_date: formatDateForInput(task.value.deadline),
      due_time: formatTimeForInput(task.value.deadline),
      start_date: formatDateForInput(task.value.start_date),
      start_time: formatTimeForInput(task.value.start_date),
      assigned_to: task.value.assigned_to,
      is_reviewed: !!task.value.is_reviewed,
      is_public: !!task.value.is_public
    };
};

const updateTask = async () => {
  if (!task.value) return;

  const formatToBackend = (dateStr, timeStr) => {
    if (!dateStr) return null;
    const time = timeStr || '00:00';
    return `${dateStr} ${time}:00`;
  };

  const deadline = formatToBackend(editedTask.value.due_date, editedTask.value.due_time || '23:59');
  const start_date = formatToBackend(editedTask.value.start_date, editedTask.value.start_time || '09:00');

  const hasChanged = 
    editedTask.value.title !== task.value.title || 
    editedTask.value.description !== task.value.description ||
    editedTask.value.priority !== task.value.priority ||
    editedTask.value.status !== task.value.status ||
    editedTask.value.assigned_to !== task.value.assigned_to ||
    editedTask.value.is_reviewed !== !!task.value.is_reviewed ||
    editedTask.value.is_public !== !!task.value.is_public ||
    editedTask.value.due_date !== (task.value.deadline ? formatDate(task.value.deadline) : '') ||
    editedTask.value.due_time !== (task.value.deadline ? formatTime(task.value.deadline) : '') ||
    editedTask.value.start_date !== (task.value.start_date ? formatDate(task.value.start_date) : '') ||
    editedTask.value.start_time !== (task.value.start_date ? formatTime(task.value.start_date) : '');
  
  if (!hasChanged) return;
  
  isSyncing.value = true;
  const previousState = JSON.parse(JSON.stringify(task.value));

  try {
    const dataToSend = { ...editedTask.value, deadline, start_date };
    
    // 1. Optimistic UI Update
    task.value = { ...task.value, ...dataToSend };

    // 2. Background Sync
    const response = await axios.patch(`/api/tasks/${props.taskId}`, dataToSend);
    
    // Update with final server state (includes activity logs, real IDs, etc.)
    task.value = { ...task.value, ...response.data };
    
    // Subtle visual confirmation
    const el = document.querySelector('.drawer-content');
    if (el) {
       el.classList.add('animate-pulse-success');
       setTimeout(() => el.classList.remove('animate-pulse-success'), 400);
    }

    if (response.data.calendar_sync_status) {
        calendarStatus.value = 'Google Linked';
        clearTimeout(syncTimeout.value);
        syncTimeout.value = setTimeout(() => { calendarStatus.value = null; }, 3000);
    }

    emit('updated');
  } catch (error) {
    console.error('Update failed', error);
    // 3. Rollback UI
    task.value = previousState;
    syncLocalStateFromTask(); // Restores editedTask from task
    ui.notify('Failed to sync changes. Check connection.', 'error');
  } finally {
    isSyncing.value = false;
  }
};

const fetchWorkspaceMembers = async (workspaceId) => {
  try {
    const response = await axios.get(`/api/share/members/workspace/${workspaceId}`);
    workspaceMembers.value = response.data;
  } catch (error) {
    console.error('Failed to fetch workspace members', error);
  }
};

const toggleSubtask = async (sub) => {
  if (sub.is_optimistic) return; // Wait for save

  const newStatus = sub.status === 'done' ? 'todo' : 'done';
  const originalStatus = sub.status;
  
  // Optimistic Update
  sub.status = newStatus;
  
  // Update parent task object directly without fetch if possible
  const subIndex = task.value.subtasks.findIndex(s => s.id === sub.id);
  if (subIndex !== -1) {
      task.value.subtasks[subIndex].status = newStatus;
  }

  try {
    await axios.patch(`/api/tasks/${sub.id}`, { status: newStatus });
    // Silent event for parent to know something changed
    emit('updated');
  } catch (error) { 
    sub.status = originalStatus;
    if (subIndex !== -1) task.value.subtasks[subIndex].status = originalStatus;
    ui.notify('Update failed', 'error'); 
  }
};

const deleteTask = async () => {
  const confirmed = await ui.confirm('Permanently delete this task? This action cannot be undone.', { variant: 'rose', confirmText: 'Delete' });
  if (!confirmed) return;
  try {
    await axios.delete(`/api/tasks/${props.taskId}`);
    ui.notify('Task deleted', 'success');
    emit('updated');
    emit('close');
  } catch (error) { ui.notify('Failed to delete', 'error'); }
};

const enableAddSubtask = async () => {
  showAddSubtask.value = true;
  // Wait for DOM update to focus input
  setTimeout(() => {
    newSubtaskInputRef.value?.focus();
  }, 100);
};

const cancelAddSubtask = () => {
  showAddSubtask.value = false;
  newSubtaskTitle.value = '';
};

const isCreatingSubtask = ref(false);

const createSubtask = async () => {
  const title = newSubtaskTitle.value.trim();
  if (!title) return;
  
  if (isCreatingSubtask.value) return; // Prevent double submit
  isCreatingSubtask.value = true;
  
  // Optimistic UI: Create temporary subtask
  const tempId = 'temp-' + Date.now();
  const tempSubtask = {
    id: tempId,
    title: title,
    status: 'todo',
    board_id: task.value.board_id,
    parent_id: task.value.id,
    assigned_to: null,
    is_optimistic: true,
    created_at: new Date().toISOString()
  };

  // 1. Update Local State Immediately
  if (!task.value.subtasks) task.value.subtasks = [];
  task.value.subtasks.push(tempSubtask);
  
  // Clear input immediately for rapid entry
  newSubtaskTitle.value = '';

  try {
    const payload = {
      title: title,
      parent_id: task.value.id,
      status: 'todo',
      board_id: task.value.board_id,
      priority: 'medium',
      task_type: 'general', // Explicitly set default
      is_public: true
    };
    
    // 2. Sync with Backend
    const response = await axios.post('/api/tasks', payload);
    
    // 3. Replace temp subtask with real one
    const realSubtask = response.data;
    const index = task.value.subtasks.findIndex(t => t.id === tempId);
    if (index !== -1) {
       // Graceful replacement
       task.value.subtasks.splice(index, 1, realSubtask);
    } else {
       // Fallback if list changed wildly
       task.value.subtasks.push(realSubtask);
    }
    
    emit('updated');
    
    // Re-focus input manually if needed (Vue usually keeps it focused but good to be safe)
    if (newSubtaskInputRef.value) newSubtaskInputRef.value.focus();

  } catch (error) {
    console.error('Failed to create subtask', error.response?.data || error);
    // Revert optimistic update
    const index = task.value.subtasks.findIndex(t => t.id === tempId);
    if (index !== -1) task.value.subtasks.splice(index, 1);
    
    // Restore text
    newSubtaskTitle.value = title;
    
    // Handle validation errors specifically
    if (error.response?.status === 422) {
        ui.notify('Validation failed: Correct inputs', 'warning');
    } else {
        ui.notify('Failed to save subtask', 'error');
    }
  } finally {
      isCreatingSubtask.value = false;
  }
};

const updateSubtaskTitle = async (sub) => {
  if (sub.is_optimistic) return;
  if (!sub.title.trim()) return;
  try {
    await axios.patch(`/api/tasks/${sub.id}`, { title: sub.title });
  } catch (error) {
    console.error('Failed to update subtask', error);
  }
};

const deleteSubtaskId = async (id) => {
  const confirmed = await ui.confirm('Delete this subtask?', { variant: 'rose', confirmText: 'Delete' });
  if (!confirmed) return;
  try {
     await axios.delete(`/api/tasks/${id}`);
     fetchTask();
     emit('updated');
     ui.notify('Subtask deleted', 'success');
  } catch (e) {
     ui.notify('Failed to delete subtask', 'error');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
};

const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0');
};

const formatAction = (log) => {
  if (log.action === 'updated_status') return `moved to ${log.changes.to?.replace('_', ' ')}`;
  if (log.action === 'assigned_task') return `assigned to ${log.changes.to}`;
  if (log.action.startsWith('updated_')) return `updated ${log.action.replace('updated_', '')}`;
  return log.action.replace('_', ' ');
};

const getProperUrl = (content, download = false) => {
  if (!content) return '#';
  if (content.includes('/storage/deliveries/')) {
    const filename = content.split('/').pop();
    return `/delivery-file/${filename}${download ? '?download=1' : ''}`;
  }
  return content;
};

// Lifecycle handled above
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* Scale In Animation */
.scale-in-enter-active, .scale-in-leave-active {
  transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.scale-in-enter-from, .scale-in-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(-10px);
}

.animate-pulse-success {
  animation: pulse-success 0.4s ease-out;
}

/* Instant Sidebar Transition (translateX + opacity) */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.fade-enter-active .drawer-content {
  transition: transform 0.4s cubic-bezier(0.2, 1, 0.3, 1);
}
.fade-leave-active .drawer-content {
  transition: transform 0.3s cubic-bezier(0.7, 0, 0.8, 0);
}

.fade-enter-from .drawer-content {
  transform: translateX(100%);
}
.fade-leave-to .drawer-content {
  transform: translateX(100%);
}

@keyframes pulse-success {
  0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>
