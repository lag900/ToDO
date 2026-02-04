<template>
  <div class="bg-transparent p-0">
    <!-- Desktop Calendar View -->
    <div v-if="!isMobile">
      <!-- Calendar Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-6">
          <h2 class="text-2xl font-heading font-extrabold text-slate-800 dark:text-white capitalize">{{ monthYearLabel }}</h2>
          <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
            <button @click="changeMonth(-1)" class="p-2 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all text-slate-400 hover:text-indigo-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button @click="changeMonth(1)" class="p-2 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all text-slate-400 hover:text-indigo-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
          <p class="text-[10px] font-heading font-extrabold uppercase tracking-widest text-slate-400">Monthly View</p>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="grid grid-cols-7 gap-3 sm:gap-4">
        <!-- Weekday Labels -->
        <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="text-center mb-2">
          <span class="text-[13px] font-heading font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ day }}</span>
        </div>

        <!-- Calendar Days -->
        <div v-for="(cell, index) in calendarCells" :key="index" 
             class="min-h-[240px] p-4 bg-white dark:bg-slate-900 rounded-[1.5rem] border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-md flex flex-col relative group"
             :class="[
               cell.isOtherMonth ? 'opacity-50 grayscale bg-slate-50 dark:bg-slate-950' : '',
               cell.isToday ? 'ring-2 ring-indigo-500 ring-offset-2 dark:ring-offset-slate-950' : ''
             ]">
          
          <div class="flex justify-between items-start mb-4 shrink-0">
            <span :class="[
              'text-2xl font-heading font-bold leading-none',
              cell.isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-200'
            ]">
              {{ cell.day }}
            </span>
            <span v-if="cell.isToday" class="text-[10px] uppercase font-black text-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded-md">Today</span>
          </div>

          <div class="flex-1 overflow-y-auto space-y-2 pr-1 custom-scrollbar scrollbar-hide hover:scrollbar-default">
            <div v-for="task in getTasksForDay(cell.date)" :key="task.id" 
                 @click="$emit('task-click', task.id)"
                 class="group/task flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-white dark:hover:bg-slate-700 hover:shadow-md transition-all cursor-pointer border border-transparent hover:border-slate-100 dark:hover:border-slate-600 select-none relative overflow-hidden"
            >
              <!-- Avatar -->
              <div class="relative shrink-0">
                 <img 
                   :src="(task.working_by ? task.working_by.avatar : task.creator?.avatar) || 'https://ui-avatars.com/api/?name=' + (task.working_by ? task.working_by.display_name : task.creator?.display_name)" 
                   class="w-7 h-7 rounded-full object-cover ring-2 ring-white dark:ring-slate-800 shadow-sm"
                 >
                 <div v-if="task.status === 'in_progress'" class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-indigo-500 rounded-full ring-2 ring-white dark:ring-slate-800"></div>
              </div>

              <!-- Content -->
              <div class="min-w-0 flex-1">
                 <p class="text-[13px] font-semibold text-slate-700 dark:text-slate-200 leading-tight truncate group-hover/task:text-indigo-600 transition-colors">{{ task.title }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Calendar View (Day Focused) -->
    <div v-else class="space-y-8">
      <!-- Day Selector (Horizontal Swipe) -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
           <h2 class="text-lg font-heading font-extrabold text-slate-800 dark:text-white uppercase tracking-widest">{{ monthYearLabel }}</h2>
           <div class="flex gap-2">
              <button @click="changeMonth(-1)" class="p-2 bg-slate-100 dark:bg-slate-800 rounded-xl text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="15 18 9 12 15 6"/></svg>
              </button>
              <button @click="changeMonth(1)" class="p-2 bg-slate-100 dark:bg-slate-800 rounded-xl text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
           </div>
        </div>

        <div 
          class="flex items-center gap-3 overflow-x-auto pb-4 no-scrollbar scroll-smooth"
          @touchstart="handleTouchStart"
          @touchend="handleTouchEnd"
        >
           <button 
             v-for="cell in currentMonthDays" :key="cell.date.toISOString()"
             @click="selectedDate = cell.date"
             class="flex-shrink-0 w-16 h-20 rounded-[1.5rem] flex flex-col items-center justify-center gap-1 transition-all duration-300"
             :class="[
               isSameDay(selectedDate, cell.date) 
                 ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/30 -translate-y-1' 
                 : 'bg-slate-50 dark:bg-slate-800 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'
             ]"
           >
              <span class="text-[10px] font-heading font-extrabold uppercase tracking-widest opacity-60">{{ getDayName(cell.date) }}</span>
              <span class="text-lg font-heading font-extrabold">{{ cell.day }}</span>
              <div v-if="hasTasksForDay(cell.date)" class="w-1 h-1 rounded-full bg-current"></div>
           </button>
        </div>
      </div>

      <!-- Tasks List for Selected Day -->
      <div class="space-y-4">
         <div class="flex items-center justify-between">
            <h3 class="text-xs font-heading font-extrabold uppercase tracking-[0.2em] text-slate-400">Tasks for {{ formatShortDate(selectedDate) }}</h3>
            <span class="text-[10px] font-heading font-extrabold uppercase text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-1 rounded-md">{{ getTasksForDay(selectedDate).length }} Tasks</span>
         </div>

         <div class="space-y-3">
            <div 
              v-for="task in getTasksForDay(selectedDate)" :key="task.id"
              @click="$emit('task-click', task.id)"
              class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-[2rem] border border-slate-100 dark:border-slate-700/50 active:scale-[0.98] transition-all"
            >
               <div class="flex items-center justify-between mb-3">
                  <span :class="taskColorClass(task.priority)" class="text-[10px] font-heading font-extrabold uppercase tracking-widest px-2.5 py-1 rounded-full">
                    {{ task.priority }}
                  </span>
                  <div class="flex items-center gap-2">
                     <p class="text-[10px] font-heading font-extrabold uppercase tracking-widest text-slate-400">{{ task.status.replace('_', ' ') }}</p>
                  </div>
               </div>

               <h4 class="text-base font-bold text-slate-800 dark:text-white mb-2">{{ task.title }}</h4>

               <div class="flex items-center justify-between">
                  <!-- Work by Indicator -->
                  <div v-if="task.working_by" class="flex items-center gap-2">
                     <img :src="task.working_by.avatar || 'https://ui-avatars.com/api/?name=' + task.working_by.display_name" class="w-6 h-6 rounded-full border-2 border-indigo-500">
                     <span class="text-[10px] font-heading font-extrabold uppercase tracking-widest text-indigo-600">Work by · {{ task.working_by.display_name }}</span>
                  </div>
                  <div v-else class="flex items-center gap-2 opacity-50">
                     <img :src="task.creator?.avatar || 'https://ui-avatars.com/api/?name=' + task.creator?.display_name" class="w-6 h-6 rounded-full grayscale">
                     <span class="text-[10px] font-heading font-extrabold uppercase tracking-widest text-slate-400">Waiting Room</span>
                  </div>
               </div>
            </div>

            <div v-if="getTasksForDay(selectedDate).length === 0" class="py-12 text-center bg-slate-50 dark:bg-slate-800/30 rounded-[2.5rem] border border-dashed border-slate-200 dark:border-slate-800">
               <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto text-slate-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
               <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Clear for takeoff!</p>
            </div>
         </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  tasks: { type: Array, default: () => [] },
  monthYear: { type: String, default: '' },
  isMobile: { type: Boolean, default: false },
  globalMode: { type: Boolean, default: false }
});

const emit = defineEmits(['task-click', 'update:monthYear']);

const currentDate = ref(new Date());
const selectedDate = ref(new Date());
const touchStartX = ref(0);

// Sync internal date with prop
watch(() => props.monthYear, (newVal) => {
  if (newVal) {
    const [year, month] = newVal.split('-');
    const newDate = new Date(year, month - 1, 1);
    if (newDate.getMonth() !== currentDate.value.getMonth() || newDate.getFullYear() !== currentDate.value.getFullYear()) {
      currentDate.value = newDate;
      // Also update selectedDate to first day of new month if current selected is not in same month
      if (selectedDate.value.getMonth() !== currentDate.value.getMonth()) {
        selectedDate.value = new Date(year, month - 1, 1);
      }
    }
  }
}, { immediate: true });

const monthYearLabel = computed(() => {
  return currentDate.value.toLocaleString('default', { month: 'long', year: 'numeric' });
});

const currentMonthDays = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  const lastDate = new Date(year, month + 1, 0).getDate();
  const days = [];
  const today = new Date();

  for (let i = 1; i <= lastDate; i++) {
    const date = new Date(year, month, i);
    days.push({
      day: i,
      date: date,
      isToday: date.toDateString() === today.toDateString()
    });
  }
  return days;
});

const calendarCells = computed(() => {
  const cells = [];
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();

  const firstDay = new Date(year, month, 1).getDay();
  const lastDate = new Date(year, month + 1, 0).getDate();
  const prevLastDate = new Date(year, month, 0).getDate();

  // Prev month padding
  for (let i = firstDay - 1; i >= 0; i--) {
    cells.push({
      day: prevLastDate - i,
      date: new Date(year, month - 1, prevLastDate - i),
      isOtherMonth: true
    });
  }

  // Current month
  const today = new Date();
  for (let i = 1; i <= lastDate; i++) {
    const date = new Date(year, month, i);
    cells.push({
      day: i,
      date: date,
      isOtherMonth: false,
      isToday: date.toDateString() === today.toDateString()
    });
  }

  // Next month padding
  const paddingNeeded = 42 - cells.length;
  for (let i = 1; i <= paddingNeeded; i++) {
    cells.push({
      day: i,
      date: new Date(year, month + 1, i),
      isOtherMonth: true
    });
  }

  return cells;
});

const changeMonth = (val) => {
  const newDate = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + val, 1);
  currentDate.value = newDate;
  selectedDate.value = new Date(newDate.getFullYear(), newDate.getMonth(), 1);
  const year = newDate.getFullYear();
  const month = String(newDate.getMonth() + 1).padStart(2, '0');
  emit('update:monthYear', `${year}-${month}`);
};

const getTasksForDay = (date) => {
  if (!date) return [];
  const dateStr = date.toDateString();
  return props.tasks.filter(task => {
    const taskDate = new Date(task.deadline || task.created_at);
    return taskDate.toDateString() === dateStr;
  });
};

const hasTasksForDay = (date) => {
  return getTasksForDay(date).length > 0;
};

const isSameDay = (d1, d2) => {
  return d1 && d2 && d1.toDateString() === d2.toDateString();
};

const getDayName = (date) => {
  return date.toLocaleString('default', { weekday: 'short' }).slice(0, 3);
};

const formatShortDate = (date) => {
  if (!date) return '';
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
};

const handleTouchStart = (e) => {
  touchStartX.value = e.touches[0].clientX;
};

const handleTouchEnd = (e) => {
  const touchEndX = e.changedTouches[0].clientX;
  const diff = touchStartX.value - touchEndX;
  
  if (Math.abs(diff) > 70) {
    const currentDay = selectedDate.value.getDate();
    const lastDay = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0).getDate();
    
    if (diff > 0 && currentDay < lastDay) {
      // Swipe Left -> Next Day
      selectedDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), currentDay + 1);
    } else if (diff < 0 && currentDay > 1) {
      // Swipe Right -> Prev Day
      selectedDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), currentDay - 1);
    }
  }
};

const taskColorClass = (priority) => {
  switch (priority) {
    case 'urgent': return 'bg-rose-500 text-white';
    case 'high': return 'bg-orange-500 text-white';
    case 'medium': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400';
    default: return 'bg-slate-100 text-slate-500 dark:bg-slate-800';
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
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
