<template>
  <div class="relative z-50">
    <!-- Switcher Button -->
    <button 
      @click.stop="isOpen = !isOpen"
      class="flex items-center gap-3 bg-white dark:bg-slate-900 px-3 py-2 sm:py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-indigo-500 hover:shadow-lg transition-all duration-300 group min-w-[200px] h-11 lg:h-auto"
    >
      <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 transition-transform">
        <span v-if="workspaceStore.globalMode" class="text-lg">🌍</span>
        <span v-else-if="currentWorkspace?.type === 'Personal'" class="text-lg">🧍</span>
        <span v-else-if="currentWorkspace?.type === 'Work'" class="text-lg">💼</span>
        <span v-else class="text-lg">👥</span>
      </div>
      
      <div class="flex-1 text-left">
        <div class="flex items-center gap-1.5 mb-1">
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-none">Context</p>
          <span v-if="currentWorkspace?.pivot?.role" class="text-[8px] px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 rounded-full font-black uppercase tracking-tighter">
            {{ currentWorkspace.pivot.role }}
          </span>
        </div>
        <p class="text-base font-bold text-slate-800 dark:text-white leading-none truncate w-32">
          {{ workspaceStore.globalMode ? 'Global View' : (currentWorkspace?.name || 'Loading...') }}
        </p>
      </div>

      <svg 
        xmlns="http://www.w3.org/2000/svg" 
        class="w-4 h-4 text-slate-400 transition-transform duration-300"
        :class="{ 'rotate-180': isOpen }"
        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Context Menu -->
    <div 
      v-if="isOpen"
      v-click-outside="() => isOpen = false"
      class="absolute top-full left-0 mt-2 w-72 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 p-2 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200"
    >
      <div class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Switch Context</div>
      
      <div class="max-h-[300px] overflow-y-auto space-y-1 my-2 custom-scrollbar">
        <button 
          v-for="workspace in workspaces" 
          :key="workspace.id"
          @click="selectContext(workspace)"
          :class="[currentWorkspace?.id === workspace.id ? 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-200 dark:border-indigo-500/30' : 'border-transparent hover:bg-slate-50 dark:hover:bg-slate-800/50']"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl border transition-all group"
        >
          <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex items-center justify-center text-lg shadow-sm">
            <span>{{ getIcon(workspace.type) }}</span>
          </div>
          
          <div class="flex-1 text-left">
            <p 
              class="text-base font-bold leading-none mb-1 transition-colors"
              :class="[currentWorkspace?.id === workspace.id ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300']"
            >
              {{ workspace.name }}
            </p>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">{{ workspace.type }}</p>
          </div>

          <div v-if="currentWorkspace?.id === workspace.id" class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
          
          <button 
            v-if="workspace.pivot?.role === 'owner'"
            @click.stop="handleConfirmDelete(workspace)"
            class="p-2 text-slate-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-all opacity-0 group-hover:opacity-100"
            title="Delete Context"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
          </button>
        </button>
      </div>

      <div class="h-px bg-slate-100 dark:bg-slate-800 my-2"></div>

      <button 
        @click="openCreate"
        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-slate-50 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 text-slate-600 dark:text-slate-300 hover:text-indigo-600 rounded-xl transition-all text-sm font-bold uppercase tracking-wider border border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-300"
      >
        <span class="text-lg">+</span> Create New Context
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useWorkspaceStore } from '../stores/workspace';
import { storeToRefs } from 'pinia';

const emit = defineEmits(['create']);

const workspaceStore = useWorkspaceStore();
const { workspaces, currentWorkspace } = storeToRefs(workspaceStore);
const isOpen = ref(false);

const getIcon = (type) => {
  switch(type) {
    case 'Personal': return '🧍';
    case 'Work': return '💼';
    case 'Company': return '🏢';
    case 'Team': return '👥';
    default: return '📁';
  }
};

const selectContext = (workspace) => {
  workspaceStore.switchWorkspace(workspace);
  isOpen.value = false;
};

const openCreate = () => {
  emit('create');
  isOpen.value = false;
};

const handleConfirmDelete = async (workspace) => {
  if (confirm(`Are you sure you want to delete "${workspace.name}"? This action cannot be undone.`)) {
    try {
      await workspaceStore.deleteWorkspace(workspace.id);
    } catch (error) {
      const msg = error.response?.data?.message || 'Failed to delete workspace. Please check your permissions or network.';
      alert(msg);
    }
  }
};

// Simple click-outside directive
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
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #475569;
}
</style>
