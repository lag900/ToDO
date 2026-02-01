<template>
  <div class="p-8 max-w-4xl mx-auto space-y-12 font-outfit min-h-screen">
    <!-- Header -->
    <header class="flex justify-between items-end">
       <div>
          <h1 class="text-4xl font-black text-slate-800 dark:text-white tracking-tight">Preferences.</h1>
          <p class="text-slate-500 font-medium">Manage your notification and workspace behaviors.</p>
       </div>
       <button @click="router.back()" class="p-3 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm hover:scale-105 transition-all">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
       </button>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
       <!-- Navigation -->
       <div class="space-y-2">
          <button class="w-full text-left px-6 py-4 rounded-2xl bg-indigo-600 text-white font-black shadow-xl shadow-indigo-500/20 text-sm tracking-widest uppercase">Notifications</button>
          <button class="w-full text-left px-6 py-4 rounded-2xl text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 font-black text-sm tracking-widest uppercase transition-all">Profile (Soon)</button>
          <button class="w-full text-left px-6 py-4 rounded-2xl text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 font-black text-sm tracking-widest uppercase transition-all">Workspaces (Soon)</button>
       </div>

       <!-- Content -->
       <div class="md:col-span-2 space-y-10">
          <section class="space-y-6">
             <div class="flex items-center gap-2 mb-6">
                <div class="w-1.5 h-6 bg-indigo-600 rounded-full"></div>
                <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-wider">Email Alerts</h2>
             </div>

             <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <!-- Toggle Global -->
                <div class="p-8 flex items-center justify-between border-b border-slate-50 dark:border-slate-800/50 bg-slate-50/50 dark:bg-slate-800/20">
                   <div>
                      <h3 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-xs">Enable Email Notifications</h3>
                      <p class="text-xs text-slate-400 mt-1 font-bold">Receive alerts for important workspace activity.</p>
                   </div>
                   <div 
                      @click="settings.email_enabled = !settings.email_enabled"
                      :class="['w-14 h-7 rounded-full relative transition-all cursor-pointer', settings.email_enabled ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700']"
                   >
                      <div :class="['absolute top-1 left-1 w-5 h-5 rounded-full bg-white transition-all shadow-md', settings.email_enabled ? 'translate-x-7' : '']"></div>
                   </div>
                </div>

                <!-- Detail Settings -->
                <div :class="['p-8 space-y-8 transition-all', !settings.email_enabled ? 'opacity-30 pointer-events-none' : '']">
                   <div>
                      <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Notify me when...</label>
                      <div class="space-y-4">
                         <div v-for="type in notificationTypes" :key="type.id" 
                           @click="toggleType(type.id)"
                           class="flex items-center gap-4 group cursor-pointer"
                         >
                            <div :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all', settings.types.includes(type.id) ? 'bg-indigo-600 border-indigo-600' : 'border-slate-200 dark:border-slate-700 group-hover:border-indigo-400']">
                               <svg v-if="settings.types.includes(type.id)" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span :class="['text-sm font-bold transition-all', settings.types.includes(type.id) ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400']">{{ type.label }}</span>
                         </div>
                      </div>
                   </div>

                   <div class="pt-6 border-t border-slate-50 dark:border-slate-800/50 flex items-center justify-between">
                      <div>
                         <h3 class="font-black text-slate-700 dark:text-slate-200 uppercase tracking-widest text-[10px]">Exclude my own actions</h3>
                         <p class="text-[10px] text-slate-400 font-bold">Don't notify me for tasks I create myself.</p>
                      </div>
                      <div 
                         @click="settings.exclude_self = !settings.exclude_self"
                         :class="['w-10 h-5 rounded-full relative transition-all cursor-pointer', settings.exclude_self ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700']"
                      >
                         <div :class="['absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white transition-all shadow-sm', settings.exclude_self ? 'translate-x-5' : '']"></div>
                      </div>
                   </div>
                </div>
             </div>

             <div class="flex justify-end gap-4 mt-8">
                <button 
                  @click="saveSettings" 
                  :disabled="loading"
                  class="px-10 py-4 bg-indigo-600 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-xs shadow-xl shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 disabled:opacity-50"
                >
                   {{ loading ? 'SAVING...' : 'SAVE CHANGES' }}
                </button>
             </div>
          </section>
       </div>
    </div>

    <!-- Feedback Toast -->
    <transition name="fade">
       <div v-if="showToast" class="fixed bottom-10 left-1/2 -translate-x-1/2 bg-slate-900 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 z-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
          <span class="text-xs font-black uppercase tracking-widest">Settings synchronized</span>
       </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const auth = useAuthStore();
const loading = ref(false);
const showToast = ref(false);

const settings = ref({
   email_enabled: true,
   types: ['task_created', 'task_updated', 'task_completed'],
   exclude_self: false
});

const notificationTypes = [
   { id: 'task_created', label: 'A new task is added to my workspace' },
   { id: 'task_updated', label: 'An existing task is modified' },
   { id: 'task_completed', label: 'A task is marked as complete' }
];

onMounted(() => {
   if (auth.user?.notification_settings) {
      settings.value = { ...settings.value, ...auth.user.notification_settings };
   }
});

const toggleType = (id) => {
   const index = settings.value.types.indexOf(id);
   if (index > -1) {
      settings.value.types.splice(index, 1);
   } else {
      settings.value.types.push(id);
   }
};

const saveSettings = async () => {
   loading.value = true;
   try {
      await axios.patch('/api/user/settings', {
         notification_settings: settings.value
      });
      await auth.fetchUser();
      showToast.value = true;
      setTimeout(() => showToast.value = false, 3000);
   } catch (error) {
      console.error('Failed to save settings', error);
   } finally {
      loading.value = false;
   }
};
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
.fade-enter-active, .fade-leave-active { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translate(-50%, 20px); }
</style>
