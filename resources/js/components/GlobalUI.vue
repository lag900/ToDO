<template>
  <div class="fixed inset-0 z-[1000000] pointer-events-none">
    <!-- Toasts / Notifications -->
    <div class="absolute top-6 left-1/2 -translate-x-1/2 flex flex-col gap-3 items-center w-full max-w-sm px-4">
      <transition-group name="toast">
        <div 
          v-for="n in ui.notifications" :key="n.id"
          class="pointer-events-auto w-full bg-white dark:bg-slate-900 shadow-2xl rounded-2xl p-4 border border-slate-100 dark:border-slate-800 flex items-center gap-4 animate-in slide-in-from-top duration-300"
        >
          <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', statusColor(n.type)]">
            <component :is="statusIcon(n.type)" class="w-5 h-5 text-white" />
          </div>
          <p class="text-xs font-black uppercase tracking-widest text-slate-800 dark:text-white flex-1">{{ n.message }}</p>
          <button @click="ui.removeNotification(n.id)" class="p-1 px-2 text-slate-300 hover:text-slate-500">
             <XIcon class="w-4 h-4" />
          </button>
        </div>
      </transition-group>
    </div>

    <!-- Global Confirmation Dialog -->
    <transition name="fade">
      <div v-if="ui.confirmData" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm pointer-events-auto flex items-center justify-center p-6">
        <div class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-8 border border-slate-100 dark:border-slate-800 animate-in zoom-in-95 duration-300 relative overflow-hidden">
           
           <div :class="['w-16 h-16 rounded-[1.5rem] mb-6 flex items-center justify-center text-white shadow-xl', variantBg(ui.confirmData.variant)]">
              <component :is="variantIcon(ui.confirmData.variant)" class="w-8 h-8" />
           </div>

           <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight mb-2">{{ ui.confirmData.title }}</h3>
           <p class="text-sm font-bold text-slate-500 dark:text-slate-400 leading-relaxed mb-8">{{ ui.confirmData.message }}</p>

           <div class="flex gap-3">
              <button 
                @click="ui.closeConfirm(false)" 
                class="flex-1 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 font-black uppercase tracking-[0.2em] rounded-2xl text-[10px] hover:bg-slate-200 transition-all active:scale-95"
              >
                 {{ ui.confirmData.cancelText }}
              </button>
              <button 
                @click="ui.closeConfirm(true)" 
                :class="['flex-[1.5] py-4 text-white font-black uppercase tracking-[0.2em] rounded-2xl text-[10px] shadow-xl transition-all active:scale-95', variantBtn(ui.confirmData.variant)]"
              >
                 {{ ui.confirmData.confirmText }}
              </button>
           </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { useUIStore } from '../stores/ui';
import { 
  AlertCircle as AlertIcon, 
  CheckCircle as CheckIcon, 
  Info as InfoIcon,
  X as XIcon,
  Trash2 as TrashIcon,
  AlertTriangle as WarningIcon,
  HelpCircle as QuestionIcon
} from 'lucide-vue-next';

const ui = useUIStore();

const statusColor = (type) => {
  switch(type) {
    case 'success': return 'bg-emerald-500';
    case 'error': return 'bg-rose-500';
    case 'warning': return 'bg-amber-500';
    default: return 'bg-indigo-600';
  }
};

const statusIcon = (type) => {
  switch(type) {
    case 'success': return CheckIcon;
    case 'error': return AlertIcon;
    case 'warning': return WarningIcon;
    default: return InfoIcon;
  }
};

const variantBg = (v) => {
  switch(v) {
    case 'rose': return 'bg-rose-500 shadow-rose-500/20';
    case 'emerald': return 'bg-emerald-500 shadow-emerald-500/20';
    case 'amber': return 'bg-amber-500 shadow-amber-500/20';
    default: return 'bg-indigo-600 shadow-indigo-500/20';
  }
};

const variantBtn = (v) => {
  switch(v) {
    case 'rose': return 'bg-rose-500 shadow-rose-500/20 hover:bg-rose-600';
    case 'emerald': return 'bg-emerald-500 shadow-emerald-500/20 hover:bg-emerald-600';
    case 'amber': return 'bg-amber-500 shadow-amber-500/20 hover:bg-amber-600';
    default: return 'bg-indigo-600 shadow-indigo-500/20 hover:bg-indigo-700';
  }
};

const variantIcon = (v) => {
  switch(v) {
    case 'rose': return TrashIcon;
    case 'amber': return WarningIcon;
    case 'emerald': return CheckIcon;
    default: return QuestionIcon;
  }
};
</script>

<style scoped>
.toast-enter-active, .toast-leave-active {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.9);
}

.fade-enter-active, .fade-leave-active {
  transition: all 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
