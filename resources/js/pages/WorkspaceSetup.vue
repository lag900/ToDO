<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex items-center justify-center p-6 relative overflow-hidden font-outfit">
    <!-- Animated background elements -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/10 rounded-full blur-[120px] animate-pulse"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-500/10 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s"></div>

    <div class="max-w-2xl w-full bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl p-12 border border-slate-100 dark:border-slate-800 relative z-10">
      
      <!-- Progress Bar -->
      <div class="flex gap-2 mb-12">
        <div v-for="i in 3" :key="i" :class="[
          'h-1.5 flex-1 rounded-full transition-all duration-500',
          step >= i ? 'bg-indigo-600' : 'bg-slate-100 dark:bg-slate-800'
        ]"></div>
      </div>

      <transition name="fade-slide" mode="out-in">
        <!-- Step 1: Basic Info -->
        <div v-if="step === 1" key="step1" class="space-y-8">
          <div>
            <h1 class="text-4xl font-black text-slate-800 dark:text-white mb-4 tracking-tight">Name your universe.</h1>
            <p class="text-slate-500 dark:text-slate-400 text-lg">Give your workspace a name that inspires your team.</p>
          </div>

          <div class="space-y-6">
            <div>
              <label for="ws-name" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Workspace Name</label>
              <input 
                id="ws-name"
                name="workspace_name"
                v-model="form.name" 
                type="text" 
                placeholder="e.g. Acme Studio, Personal Roadmap" 
                class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-5 outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-lg font-bold"
              >
            </div>

            <div>
              <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4 px-1">How will you use THINKER?</label>
              <div class="grid grid-cols-3 gap-4">
                 <button v-for="type in ['Personal', 'Team', 'Company']" :key="type"
                   @click="form.type = type"
                   :class="[
                     'p-6 rounded-3xl border-2 transition-all flex flex-col items-center gap-3',
                     form.type === type 
                      ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-500/10' 
                      : 'border-slate-100 dark:border-slate-800 hover:border-indigo-200'
                   ]"
                 >
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', form.type === type ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400']">
                       <svg v-if="type === 'Personal'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                       <svg v-if="type === 'Team'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                       <svg v-if="type === 'Company'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <span :class="['text-xs font-black uppercase tracking-widest', form.type === type ? 'text-indigo-600' : 'text-slate-400']">{{ type }}</span>
                 </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Planning Intent -->
        <div v-else-if="step === 2" key="step2" class="space-y-8">
          <div>
            <h1 class="text-4xl font-black text-slate-800 dark:text-white mb-4 tracking-tight">What's the plan?</h1>
            <p class="text-slate-500 dark:text-slate-400 text-lg">We'll tailor your experience to match your goals.</p>
          </div>

          <div class="grid grid-cols-1 gap-4">
             <button v-for="intent in intentions" :key="intent.id"
               @click="form.intent = intent.name"
               :class="[
                 'p-6 rounded-[2rem] border-2 transition-all flex items-center gap-6 text-left',
                 form.intent === intent.name 
                  ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-500/10' 
                  : 'border-slate-100 dark:border-slate-800 hover:border-indigo-200'
               ]"
             >
                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center text-2xl', form.intent === intent.name ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-400']">
                   {{ intent.emoji }}
                </div>
                <div>
                   <h3 :class="['font-black text-lg', form.intent === intent.name ? 'text-indigo-600' : 'text-slate-700 dark:text-slate-200']">{{ intent.name }}</h3>
                   <p class="text-sm text-slate-400 font-medium">{{ intent.desc }}</p>
                </div>
             </button>
          </div>
        </div>

        <!-- Step 3: Initial Settings -->
        <div v-else-if="step === 3" key="step3" class="space-y-8">
          <div>
            <h1 class="text-4xl font-black text-slate-800 dark:text-white mb-4 tracking-tight">Fine-tuning.</h1>
            <p class="text-slate-500 dark:text-slate-400 text-lg">Enable the features you need for your workflow.</p>
          </div>

          <div class="space-y-4">
             <div v-for="(val, key) in form.settings" :key="key" 
               @click="form.settings[key] = !val"
               class="p-6 rounded-3xl border-2 border-slate-100 dark:border-slate-800 flex items-center justify-between cursor-pointer hover:border-indigo-200 transition-all"
             >
                <div class="flex items-center gap-4">
                   <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', val ? 'bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400']">
                      <svg v-if="key === 'enable_priorities'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                      <svg v-if="key === 'enable_due_dates'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                      <svg v-if="key === 'use_default_statuses'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="M15 3v18"/><path d="M3 9h18"/><path d="M3 15h18"/></svg>
                   </div>
                   <div>
                      <h3 class="font-black text-slate-700 dark:text-slate-200 uppercase tracking-widest text-xs">{{ formatKey(key) }}</h3>
                      <p class="text-xs text-slate-400 font-bold">{{ val ? 'ENABLED' : 'DISABLED' }}</p>
                   </div>
                </div>
                <div :class="['w-12 h-6 rounded-full transition-all relative', val ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700']">
                   <div :class="['absolute top-1 left-1 w-4 h-4 rounded-full bg-white transition-all', val ? 'translate-x-6' : '']"></div>
                </div>
             </div>
          </div>
        </div>
      </transition>

      <!-- Footer Actions -->
      <div class="mt-12 flex flex-col gap-4">
        <p v-if="!isStepValid && step === 1" class="text-rose-500 text-[10px] font-black uppercase tracking-widest text-center animate-bounce">
          Name must be at least 3 characters
        </p>
        <div class="flex gap-4">
          <button 
            type="button"
            v-if="step > 1" 
            @click="step--" 
            class="flex-1 px-8 py-5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-3xl font-black uppercase tracking-widest text-xs hover:bg-slate-200 transition-all"
          >
            Back
          </button>
          <button 
            type="button"
            v-if="step < 3" 
            @click="nextStep" 
            :disabled="!isStepValid" 
            class="flex-[2] px-8 py-5 bg-indigo-600 text-white rounded-3xl font-black uppercase tracking-widest text-xs shadow-xl shadow-indigo-500/30 hover:bg-indigo-700 disabled:opacity-30 disabled:cursor-not-allowed transition-all"

          >
            Continue
          </button>
          <button 
            type="button"
            v-else 
            @click="finishSetup" 
            :disabled="loading || !isStepValid" 
            class="flex-[2] px-8 py-5 bg-emerald-600 text-white rounded-3xl font-black uppercase tracking-widest text-xs shadow-xl shadow-emerald-500/30 hover:bg-emerald-700 disabled:opacity-50 transition-all"

          >
            {{ loading ? 'BUILDING...' : 'FINISH SETUP' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const step = ref(1);
const loading = ref(false);

const form = ref({
  name: '',
  type: 'Personal',
  intent: 'Daily tasks',
  settings: {
    enable_priorities: true,
    enable_due_dates: true,
    use_default_statuses: true
  }
});

const intentions = [
  { id: 1, name: 'Daily tasks', desc: 'Focus on getting things done day by day.', emoji: '🚀' },
  { id: 2, name: 'Monthly planning', desc: 'Strategic view of your projects and deadlines.', emoji: '📅' },
  { id: 3, name: 'Company goals', desc: 'High-level OKRs and team alignment.', emoji: '🏢' },
  { id: 4, name: 'Client projects', desc: 'Manage deliverables and billing cycles.', emoji: '💼' }
];

const isStepValid = computed(() => {
  if (step.value === 1) {
    return form.value.name && form.value.name.trim().length >= 3 && form.value.type;
  }
  if (step.value === 2) {
    return !!form.value.intent;
  }
  return true;
});

const nextStep = () => {
  if (isStepValid.value) {
    step.value++;
  }
};

const formatKey = (key) => {
  return key.replace(/_/g, ' ');
};

const finishSetup = async () => {
  const auth = useAuthStore();
  loading.value = true;
  try {
    await axios.post('/api/workspaces', form.value);
    // Refresh user state to update workspaces_count
    await auth.fetchUser();
    router.push('/');
  } catch (error) {
    console.error('Setup failed', error);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }

.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
</style>
