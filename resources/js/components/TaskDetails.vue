<template>
  <div v-if="task" class="fixed inset-0 z-[10000] flex justify-end">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="$emit('close')"></div>
    
    <!-- Drawer -->
    <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 h-full shadow-2xl flex flex-col transform transition-transform duration-300">
      
      <!-- Status Banner if Completed -->
      <div v-if="task.status === 'done'" class="bg-[var(--color-status-done)] text-white px-6 py-2 flex items-center gap-2 justify-center text-xs font-heading font-bold uppercase tracking-[0.3em] relative overflow-hidden group">
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

          <select 
            v-model="editedTask.priority" 
            @change="updateTask"
            :disabled="!canEditFull"
            :class="[priorityClass(editedTask.priority), {'cursor-not-allowed opacity-80': !canEditFull}]" 
            class="text-[10px] font-heading font-extrabold uppercase px-3 py-1 rounded-full tracking-widest border-none outline-none cursor-pointer appearance-none"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>

          <select 
            v-model="editedTask.status" 
            @change="updateTask"
            :disabled="!canChangeStatus"
            class="text-[10px] font-heading font-extrabold uppercase px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg tracking-widest border-none outline-none cursor-pointer"
            :class="{'cursor-not-allowed opacity-80': !canChangeStatus}"
          >
            <option value="todo">Plan</option>
            <option value="in_progress">Work</option>
            <option value="testing">Review</option>
            <option value="done">Complete</option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <div class="hidden sm:flex items-center gap-2 mr-4">
             <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M8 7h6"/><path d="M8 11h8"/></svg>
             <p class="text-[10px] font-heading font-bold text-slate-400 uppercase tracking-[0.2em]">TASK-{{ task.id }}</p>
          </div>
          <button v-if="canDelete" @click="deleteTask" class="p-2 hover:bg-rose-50 dark:hover:bg-rose-500/10 text-slate-300 hover:text-rose-500 rounded-xl transition-all" title="Delete Task">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
          </button>
          <button @click="$emit('close')" class="hidden sm:block p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto p-8 space-y-10 custom-scrollbar">
        <!-- Title & Description -->
        <section>
          <input 
            v-model="editedTask.title" 
            @blur="updateTask"
            :disabled="!canEditFull"
            class="w-full text-3xl font-heading font-bold text-slate-900 dark:text-white bg-transparent border-none outline-none focus:ring-0 mb-4 p-0"
            :class="{'cursor-not-allowed': !canEditFull}"
            placeholder="Task Title"
          >
          <textarea 
            v-model="editedTask.description" 
            @blur="updateTask"
            :disabled="!canEditFull"
            class="w-full text-base text-slate-500 dark:text-slate-400 bg-transparent border-none outline-none focus:ring-0 p-0 resize-none min-h-[100px]"
            :class="{'cursor-not-allowed': !canEditFull}"
            placeholder="Add a detailed description..."
          ></textarea>
        </section>

        <!-- Delivery Section -->
        <section v-if="task.deliveries?.length || canEditFull" class="p-8 bg-slate-50 dark:bg-slate-800/40 rounded-[2rem] border border-slate-100 dark:border-slate-800 space-y-6">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-heading font-bold uppercase tracking-[0.2em] text-slate-400">Task Deliveries</h3>
            <button 
              v-if="canEditFull && task.deliveries?.length > 0" 
              @click="showDeliveryModal = true"
              class="text-[10px] font-heading font-bold uppercase tracking-widest px-4 py-2 bg-emerald-500 text-white rounded-xl shadow-lg shadow-emerald-500/20 hover:scale-105 hover:bg-emerald-600 transition-all flex items-center gap-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
              Add New Delivery Item
            </button>
          </div>

          <div v-if="task.deliveries?.length" class="space-y-10">
            <div v-for="delivery in task.deliveries" :key="delivery.id" class="space-y-6 relative pl-6 border-l-2 border-emerald-500/20">
              <!-- Delivery Dot -->
              <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-emerald-500"></div>

              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Package from {{ delivery.user?.display_name }}</p>
                    <span class="text-[8px] font-black uppercase text-slate-400 px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded">{{ formatDate(delivery.created_at) }}</span>
                  </div>
                  
                  <!-- Delivery Delete Button -->
                  <button 
                    v-if="delivery.user_id === auth.user.id || role === 'owner'"
                    @click="deleteDelivery(delivery.id)"
                    class="p-1 px-2 text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white rounded-lg transition-all"
                  >
                    Delete Package
                  </button>
                </div>

                <div v-if="delivery.notes" class="bg-white dark:bg-slate-900/50 p-4 rounded-2xl text-sm italic text-slate-600 dark:text-slate-300 border border-slate-100 dark:border-slate-800/50">
                  {{ delivery.notes }}
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div 
                    v-for="item in delivery.items" 
                    :key="item.id" 
                    @click="openItem(getProperUrl(item.content))"
                    :title="'Click to open ' + item.name"
                    class="flex items-center gap-3 p-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-[2rem] hover:border-emerald-500/50 hover:shadow-lg hover:shadow-emerald-500/5 transition-all group relative cursor-pointer select-none"
                  >
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 shrink-0">
                      <!-- Link Icon -->
                      <svg v-if="item.type === 'link'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                      <!-- PDF Icon -->
                      <svg v-else-if="item.name.toLowerCase().endsWith('.pdf')" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h3a1.5 1.5 0 0 0 0-3H9v6"/><path d="M5 12v6"/><path d="M5 15h3"/></svg>
                      <!-- Generic File Icon -->
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div class="flex-1 min-w-0 pr-8">
                      <p class="text-[11px] font-black text-slate-800 dark:text-white truncate uppercase tracking-tight">{{ item.name }}</p>
                      <p v-if="item.description" class="text-[9px] font-bold text-slate-400 uppercase truncate">{{ item.description }}</p>
                    </div>
                    
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex gap-1">
                      <a 
                        :href="getProperUrl(item.content)" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        @click.stop
                        :download="(!item.name.toLowerCase().endsWith('.pdf') && item.type !== 'link') ? item.name : null"
                        class="p-2 bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-all"
                        :title="item.type === 'link' ? 'Open Link' : 'Open/Download File'"
                      >
                        <svg v-if="item.type === 'link' || item.name.toLowerCase().endsWith('.pdf')" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-10 bg-white/50 dark:bg-slate-900/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center gap-4">
             <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
             </div>
             <div>
               <p class="text-xs font-heading font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Professional Delivery Terminal</p>
               <p class="text-[10px] font-medium text-slate-400 mt-1">Ready for hand-over artifacts</p>
             </div>
             <button 
               v-if="canEditFull"
               @click="showDeliveryModal = true"
               class="mt-2 px-6 py-3 bg-emerald-500 text-white rounded-xl shadow-xl shadow-emerald-500/20 hover:scale-105 hover:bg-emerald-600 transition-all font-heading font-bold text-xs uppercase tracking-widest flex items-center gap-2"
             >
               <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
               Process Delivery
             </button>
          </div>
        </section>

        <!-- Planning Section (Dates & Assignee) -->
        <section class="grid grid-cols-2 gap-8 py-6 border-y border-slate-50 dark:border-slate-800/50">
          <div class="space-y-4">
            <div class="space-y-2">
              <label class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Start Date</label>
              <div class="relative flex items-center">
                <input 
                  type="date" 
                  v-model="editedTask.start_date" 
                  @change="updateTask"
                  class="w-full bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl px-4 py-3 text-base font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-indigo-500/20"
                >
              </div>
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Assignee</label>
              <select 
                v-model="editedTask.assigned_to" 
                @change="updateTask"
                :disabled="!canEditFull"
                class="w-full bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl px-4 py-3 text-base font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-indigo-500/20 appearance-none cursor-pointer"
                :class="{'cursor-not-allowed opacity-80': !canEditFull}"
              >
                <option :value="null">Unassigned</option>
                <option v-for="member in workspaceMembers" :key="member.id" :value="member.id">
                  {{ member.display_name }}
                </option>
              </select>
               <p v-if="task.status === 'in_progress' && task.working_by && task.working_by.id !== auth.user?.id" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mt-2 px-1 animate-pulse">
                In Work by {{ task.working_by.display_name }}
              </p>
              <p v-if="task.assigned_by" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-1 px-1">
                Last Assigned by {{ task.assigned_by.display_name }}
              </p>
            </div>
          </div>
          <div class="space-y-2">
            <label class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Due Date</label>
            <div class="flex gap-2">
              <input 
                type="date" 
                v-model="editedTask.due_date" 
                @change="updateTask"
                class="flex-1 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl px-4 py-3 text-base font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-indigo-500/20"
              >
              <input 
                type="time" 
                v-model="editedTask.due_time" 
                @change="updateTask"
                class="w-24 bg-slate-50 dark:bg-slate-800/50 border-none rounded-xl px-4 py-3 text-base font-bold text-slate-700 dark:text-slate-200 outline-none focus:ring-2 focus:ring-indigo-500/20"
              >
            </div>
          </div>
        </section>

        <!-- Sub-tasks -->
        <section class="space-y-4">
          <div class="flex items-center justify-between">
            <div v-if="canEditFull" class="flex items-center gap-2">
              <h3 class="text-sm font-heading font-bold uppercase tracking-[0.2em] text-slate-400">Sub-tasks</h3>
              <span v-if="task.subtasks?.length" class="text-[10px] bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full text-slate-400 font-bold">{{ task.subtasks.length }}</span>
            </div>
            <h3 v-else class="text-sm font-heading font-bold uppercase tracking-[0.2em] text-slate-400">Sub-tasks</h3>

            <button v-if="canEditFull" @click="showAddSubtask = true" class="text-[10px] font-heading font-bold text-[var(--color-brand)] hover:opacity-80 uppercase tracking-widest flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
              Add Sub-task
            </button>
          </div>
          
          <div v-if="task.subtasks?.length" class="space-y-2">
            <div v-for="sub in task.subtasks" :key="sub.id" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 group hover:border-indigo-500/30 transition-all">
                <button @click.stop="toggleSubtask(sub)" class="w-5 h-5 rounded-md border-2 border-slate-200 dark:border-slate-700 flex items-center justify-center transition-all hover:border-indigo-500">
                  <div class="w-2 h-2 rounded-sm bg-indigo-500 opacity-0 group-hover:opacity-20" :class="{'opacity-100': sub.status === 'done'}"></div>
                </button>
               <span class="flex-1 text-sm font-bold text-slate-700 dark:text-slate-200" :class="{'line-through text-slate-400': sub.status === 'done'}">{{ sub.title }}</span>
               <div class="flex items-center gap-2">
                 <span :class="priorityClass(sub.priority)" class="text-[9px] uppercase font-heading font-bold px-2 py-0.5 rounded-full">{{ sub.priority }}</span>
                 <div class="flex -space-x-2">
                    <img 
                      :src="sub.creator?.avatar || 'https://ui-avatars.com/api/?name=' + (sub.creator?.display_name || 'System')" 
                      :title="'Created by: ' + (sub.creator?.display_name || 'System')" 
                      class="w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-900 object-cover"
                    >
                    <img 
                      v-if="sub.assignee"
                      :src="sub.assignee?.avatar || 'https://ui-avatars.com/api/?name=' + sub.assignee?.display_name" 
                      :title="'Assigned to: ' + sub.assignee?.display_name" 
                      class="w-6 h-6 rounded-full ring-2 ring-white dark:ring-slate-900 object-cover"
                    >
                 </div>
               </div>
            </div>
          </div>

          <!-- Add Subtask Input -->
          <div v-if="showAddSubtask" class="mt-2 text-indigo-600 relative">
             <input 
               v-model="newSubtaskTitle" 
               @keyup.enter="createSubtask"
               @blur="!isCreatingSubtask && (showAddSubtask = false)"
               :disabled="isCreatingSubtask"
               class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-indigo-500/20 rounded-xl px-4 py-3 text-sm outline-none focus:border-indigo-500 transition-all pr-10"
               placeholder="Enter sub-task title..."
               autoFocus
             >
             <div v-if="isCreatingSubtask" class="absolute right-3 top-1/2 -translate-y-1/2 text-indigo-500">
               <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
               </svg>
             </div>
          </div>
        </section>

        <!-- Checklist -->
        <section class="space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-heading font-bold uppercase tracking-[0.2em] text-slate-400">Checklist</h3>
          </div>
          
          <div class="space-y-2">
            <div v-for="item in task.checklists" :key="item.id" class="flex items-center gap-3 group">
              <button 
                @click="canEditFull ? toggleChecklist(item) : (role === 'viewer' ? toggleChecklist(item) : null)" 
                class="w-5 h-5 rounded-md border-2 transition-all flex items-center justify-center" 
                :class="[
                  item.is_completed ? 'bg-emerald-500 border-emerald-500' : 'border-slate-200 dark:border-slate-700 group-hover:border-indigo-500',
                  {'cursor-not-allowed': !canEditFull && role !== 'viewer'}
                ]"
              >
                 <svg v-if="item.is_completed" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"/></svg>
              </button>
              <span class="flex-1 text-base font-medium transition-all" :class="item.is_completed ? 'text-slate-400 line-through' : 'text-slate-700 dark:text-slate-200'">{{ item.content }}</span>
            </div>
            
            <div v-if="canEditFull" class="flex items-center gap-3 mt-4 relative">
              <div class="w-5 h-5 flex items-center justify-center">
                 <svg v-if="isCreatingChecklist" class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                   <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                   <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                 </svg>
              </div>
              <input 
                v-model="newChecklistItem" 
                @keyup.enter="addChecklist" 
                :disabled="isCreatingChecklist"
                class="flex-1 bg-transparent border-none outline-none text-base text-slate-500 placeholder:text-slate-400 focus:ring-0 p-0 disabled:opacity-50"
                placeholder="Add an item..."
              >
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
                    <span class="font-bold text-slate-800 dark:text-white">{{ log.user_email || log.user?.email || 'System' }}</span>
                    <span class="text-slate-500 ml-1">did {{ formatAction(log) }} on <span class="font-bold text-slate-700 dark:text-slate-200">{{ log.task_name || task.title }}</span></span>
                  </p>
                  <p class="text-[12px] text-slate-400 font-bold uppercase tracking-widest">{{ formatDate(log.created_at) }}</p>
                </div>

            </div>
          </div>
        </section>
      </div>

      <!-- Footer Info -->
      <div class="p-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 grid grid-cols-3 gap-4">
        <div class="flex items-center gap-3">
          <img :src="task.creator?.avatar || 'https://ui-avatars.com/api/?name=' + (task.creator?.display_name || 'System')" class="w-8 h-8 rounded-full ring-2 ring-white dark:ring-slate-800 object-cover">
           <div>
            <p class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Created by</p>
            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ task.creator?.display_name }}</p>
          </div>
        </div>
        <div v-if="task.activity_logs?.length" class="flex items-center gap-3">
           <img :src="task.activity_logs[0].user?.avatar || 'https://ui-avatars.com/api/?name=' + task.activity_logs[0].user?.display_name" class="w-8 h-8 rounded-full ring-2 ring-white dark:ring-slate-800 object-cover">
            <div>
              <p class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Last Activity By</p>
              <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ task.activity_logs[0].user?.display_name }}</p>
            </div>
        </div>
        <div class="text-right flex flex-col justify-center">
            <p class="text-[10px] font-heading font-bold uppercase tracking-widest text-slate-400">Recipient</p>
            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ task.assignee?.display_name || 'Unassigned' }}</p>
            <p v-if="task.assigned_by" class="text-[9px] font-heading font-bold uppercase text-slate-400 mt-1">By: {{ task.assigned_by.display_name }}</p>
         </div>
      </div>
    </div>

    <!-- Delivery Modal -->
    <TaskDeliveryModal 
      v-if="showDeliveryModal"
      :show="showDeliveryModal"
      :task="task"
      @close="showDeliveryModal = false"
      @delivered="fetchTask(); emit('updated')"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import TaskDeliveryModal from './TaskDeliveryModal.vue';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();

const props = defineProps({
  taskId: { type: [Number, String], required: true },
  role: { type: String, default: 'viewer' }
});

const canEditFull = computed(() => props.role === 'owner' || props.role === 'editor');
const canChangeStatus = computed(() => true); // Everyone can change status
const canDelete = computed(() => props.role === 'owner'); // per user requirements "Owner ... حذف Tasks"

const emit = defineEmits(['close', 'updated']);

const task = ref(null);
const workspaceMembers = ref([]);
const editedTask = ref({ title: '', description: '', priority: '', status: '', due_date: '', due_time: '', start_date: '', assigned_to: null });
const showAddSubtask = ref(false);
const showDeliveryModal = ref(false);
const newSubtaskTitle = ref('');
const newChecklistItem = ref('');
const isCreatingSubtask = ref(false);
const isCreatingChecklist = ref(false);

const fetchTask = async () => {
  try {
    const response = await axios.get(`/api/tasks/${props.taskId}`);
    task.value = response.data;
    
    // Format dates for input[type="date"]
    const formatDateForInput = (dateStr) => dateStr ? dateStr.split('T')[0] : '';
    const formatTimeForInput = (dateStr) => {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0');
    };

    editedTask.value = { 
      title: response.data.title, 
      description: response.data.description,
      priority: response.data.priority,
      status: response.data.status,
      due_date: formatDateForInput(response.data.deadline),
      due_time: formatTimeForInput(response.data.deadline),
      start_date: formatDateForInput(response.data.start_date),
      assigned_to: response.data.assigned_to
    };

    fetchWorkspaceMembers(response.data.board.plan.workspace_id);
  } catch (error) {
    console.error('Error fetching task details', error);
  }
};

const updateTask = async () => {
  // Merge due_date and due_time
  let deadline = null;
  if (editedTask.value.due_date) {
    const time = editedTask.value.due_time || '23:59';
    deadline = `${editedTask.value.due_date} ${time}:00`;
  }

  const hasChanged = editedTask.value.title !== task.value.title || 
                    editedTask.value.description !== task.value.description ||
                    editedTask.value.priority !== task.value.priority ||
                    editedTask.value.status !== task.value.status ||
                    deadline !== (task.value.deadline ? task.value.deadline.replace('T', ' ').substring(0, 19) : null) ||
                    editedTask.value.start_date !== (task.value.start_date ? task.value.start_date.split('T')[0] : '') ||
                    editedTask.value.assigned_to !== task.value.assigned_to;
  
  if (!hasChanged) return;
  
  try {
    const dataToSend = { ...editedTask.value, deadline };
    const response = await axios.patch(`/api/tasks/${props.taskId}`, dataToSend);
    
    // If task was re-opened (status changed to todo automatically in backend)
    if (response.data.status !== editedTask.value.status && task.value.status === 'done') {
        // Refresh local status
        editedTask.value.status = response.data.status;
    }

    task.value = { ...task.value, ...response.data };
    emit('updated');
    fetchTask(); // Refresh for logs
  } catch (error) {
    console.error('Update failed', error);
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

const createSubtask = async () => {
  if (!newSubtaskTitle.value) return;
  isCreatingSubtask.value = true;
  try {
    await axios.post('/api/tasks', {
      title: newSubtaskTitle.value,
      parent_id: task.value.id,
      board_id: task.value.board_id,
      priority: 'medium',
      status: 'todo'
    });
    newSubtaskTitle.value = '';
    showAddSubtask.value = false;
    await fetchTask();
    emit('updated');
  } catch (error) {
    console.error('Subtask creation failed', error);
  } finally {
    isCreatingSubtask.value = false;
  }
};

const addChecklist = async () => {
  if (!newChecklistItem.value) return;
  isCreatingChecklist.value = true;
  try {
    await axios.post(`/api/tasks/${props.taskId}/checklists`, {
      content: newChecklistItem.value
    });
    newChecklistItem.value = '';
    await fetchTask();
    emit('updated'); // Logic to re-open if needed
  } catch (error) {
    console.error('Checklist addition failed', error);
  } finally {
    isCreatingChecklist.value = false;
  }
};

const toggleChecklist = async (item) => {
  try {
    await axios.post(`/api/checklists/${item.id}/toggle`);
    fetchTask();
    emit('updated'); // Logic to re-open if needed
  } catch (error) {
    console.error('Checklist toggle failed', error);
  }
};

const toggleSubtask = async (sub) => {
  const newStatus = sub.status === 'done' ? 'todo' : 'done';
  const oldStatus = sub.status;
  
  // Optimistic UI
  sub.status = newStatus;
  
  try {
    await axios.patch(`/api/tasks/${sub.id}`, { status: newStatus });
    fetchTask();
    emit('updated');
  } catch (error) {
    sub.status = oldStatus;
    console.error('Subtask toggle failed', error);
  }
};

const deleteDelivery = async (deliveryId) => {
  if (!confirm('Are you sure you want to delete this delivery package? All associated files and links will be removed.')) return;
  try {
    await axios.delete(`/api/deliveries/${deliveryId}`);
    fetchTask();
    emit('updated');
  } catch (error) {
    alert('Delete failed: ' + (error.response?.data?.message || 'Server error'));
  }
};

const deleteTask = async () => {
  if (!confirm('Are you sure you want to delete this task? This action cannot be undone.')) return;
  try {
    await axios.delete(`/api/tasks/${props.taskId}`);
    emit('updated');
    emit('close');
  } catch (error) {
    alert('Delete failed: ' + (error.response?.data?.message || 'Server error'));
  }
};

const formatAction = (log) => {
  switch (log.action) {
    case 'created': return 'create';
    case 'reopened': return 're-open';
    case 'updated_status': return `move from ${log.changes.from?.replace('_', ' ') || 'unknown'} to ${log.changes.to?.replace('_', ' ') || 'unknown'}`;
    case 'updated_title': return `edit title to "${log.changes.to}"`;
    case 'added_checklist_item': return `add "${log.changes.content}" to checklist`;
    case 'completed_checklist_item': return `complete checklist item "${log.changes.content}"`;
    case 'uncompleted_checklist_item': return `uncomplete checklist item "${log.changes.content}"`;
    case 'assigned_task': return `assign to ${log.changes.to}${log.changes.by ? ' by ' + log.changes.by : ''}`;
    case 'updated_priority': return `change priority to ${log.changes.to}`;
    case 'updated_description': return 'edit description';
    case 'updated_deadline': return `edit due date to ${formatDate(log.changes.to)}`;
    case 'updated_start_date': return `edit start date to ${formatDate(log.changes.to)}`;
    case 'deleted': return 'delete';
    case 'subtask_completed': return 'complete sub-task';
    default: return log.action;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  });
};

const priorityClass = (p) => {
  switch (p) {
    case 'urgent': return 'bg-rose-500 text-white';
    case 'high': return 'bg-orange-500 text-white';
    case 'medium': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400';
    default: return 'bg-slate-100 text-slate-500';
  }
};

const getProperUrl = (content) => {
  if (!content) return '#';
  if (content.startsWith('http://127.0.0.1') || content.startsWith('http://localhost')) {
      const parts = content.split('/storage/');
      if (parts.length > 1) {
          return '/storage/' + parts[1];
      }
  }
  return content;
};

const openItem = (url) => {
  window.open(url, '_blank');
};

onMounted(fetchTask);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
}
</style>
