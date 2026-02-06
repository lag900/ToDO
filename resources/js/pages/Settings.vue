<template>
  <div class="p-8 max-w-4xl mx-auto space-y-12 font-outfit min-h-screen relative">
    <!-- Global Loading State -->
    <div v-if="!isPageReady" class="fixed inset-0 bg-white dark:bg-slate-950 z-[100] flex flex-col items-center justify-center">
       <div class="w-16 h-16 border-4 border-indigo-600/20 border-t-indigo-600 rounded-full animate-spin"></div>
       <p class="mt-6 text-xs font-black uppercase tracking-[0.3em] text-slate-400 animate-pulse">Synchronizing Life...</p>
    </div>

    <div v-if="isPageReady" class="animate-in fade-in zoom-in-95 duration-500">

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
          <button 
            @click="activeTab = 'notifications'"
            :class="[activeTab === 'notifications' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50']"
            class="w-full text-left px-6 py-4 rounded-2xl font-black text-sm tracking-widest uppercase transition-all"
          >
            Notifications
          </button>
          <button 
            @click="activeTab = 'workspace'"
            :class="[activeTab === 'workspace' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50']"
            class="w-full text-left px-6 py-4 rounded-2xl font-black text-sm tracking-widest uppercase transition-all flex items-center justify-between"
          >
            <span>Workspace Behavior</span>
            <div v-if="!workspaceStore.currentWorkspace && workspaceStore.loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
          </button>
          <button 
            @click="activeTab = 'integrations'"
            :class="[activeTab === 'integrations' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50']"
            class="w-full text-left px-6 py-4 rounded-2xl font-black text-sm tracking-widest uppercase transition-all"
          >
            Integrations
          </button>
          <button class="w-full text-left px-6 py-4 rounded-2xl text-slate-400 font-black text-sm tracking-widest uppercase opacity-50 cursor-not-allowed">Profile (Soon)</button>
       </div>

       <!-- Content -->
       <div class="md:col-span-2 space-y-10 relative">
           <!-- Loading Overlay -->
           <div v-if="workspaceStore.loading && !workspaceStore.workspaces.length" class="absolute inset-0 bg-white/50 dark:bg-slate-950/50 backdrop-blur-sm z-50 flex flex-col items-center justify-center rounded-[3rem]">
              <div class="w-12 h-12 border-4 border-indigo-600/20 border-t-indigo-600 rounded-full animate-spin"></div>
              <p class="mt-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Loading Preferences...</p>
           </div>

           <!-- Notifications Tab -->
           <section v-if="activeTab === 'notifications'" class="space-y-6">
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
                          <p class="text-[10px] text-slate-400 font-bold">Don't notify me for actions I perform myself.</p>
                       </div>
                       <div 
                          @click="settings.exclude_self = !settings.exclude_self"
                          :class="['w-10 h-5 rounded-full relative transition-all cursor-pointer', settings.exclude_self ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700']"
                       >
                          <div :class="['absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white transition-all shadow-sm', settings.exclude_self ? 'translate-x-5' : '']"></div>
                       </div>
                    </div>

                    <div class="pt-6 border-t border-slate-50 dark:border-slate-800/50 flex items-center justify-between">
                       <div>
                          <h3 class="font-black text-slate-700 dark:text-slate-200 uppercase tracking-widest text-[10px]">Exclude Workspace Origin</h3>
                          <p class="text-[10px] text-slate-400 font-bold">Don't notify me for tasks I created myself (but assigned to others).</p>
                       </div>
                       <div 
                          @click="settings.exclude_tasks_created_by_me = !settings.exclude_tasks_created_by_me"
                          :class="['w-10 h-5 rounded-full relative transition-all cursor-pointer', settings.exclude_tasks_created_by_me ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700']"
                       >
                          <div :class="['absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white transition-all shadow-sm', settings.exclude_tasks_created_by_me ? 'translate-x-5' : '']"></div>
                       </div>
                    </div>
                 </div>
              </div>

              <div class="flex justify-end gap-4 mt-8">
                 <button 
                   @click="saveUserSettings" 
                   :disabled="loading"
                   class="px-10 py-4 bg-indigo-600 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-xs shadow-xl shadow-indigo-500/20 hover:bg-indigo-700 transition-all active:scale-95 disabled:opacity-50"
                 >
                    {{ loading ? 'SAVING...' : 'SAVE CHANGES' }}
                 </button>
              </div>
           </section>

           <!-- Workspace Tab -->
           <section v-if="activeTab === 'workspace'" class="space-y-6">
              <div class="flex items-center gap-2 mb-6">
                <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-wider">Workspace Behavior</h2>
              </div>

              <!-- Workspace Selector -->
              <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm p-8 space-y-4">
                  <label for="workspace-selector" class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Select Workspace to Configure</label>
                  <select 
                    id="workspace-selector"
                    name="workspace_id"
                    @change="loadWorkspaceSettings"
                    v-model="selectedWorkspaceId"
                    class="w-full h-14 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-6 text-sm font-bold shadow-sm outline-none transition-all"
                  >
                    <option v-for="ws in workspaceStore.workspaces" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
                  </select>
              </div>

                   <div v-if="activeWorkspace" class="space-y-6">
                      <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden border-t-4 border-t-emerald-500">
                        <div class="p-8 space-y-8">
                            <div class="flex items-center justify-between">
                              <div class="flex-1 pr-4">
                                  <h3 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-sm">Enable Review Column</h3>
                                  <p class="text-xs text-slate-500 mt-2 font-medium leading-relaxed">
                                    When enabled, a dedicated <b>"Review"</b> column will appear on your kanban board for 
                                    <span class="text-emerald-500 font-bold">{{ activeWorkspace.name }}</span>.
                                  </p>
                              </div>
                              <div 
                              @click="isOwner ? wsSettings.enable_review_step = !wsSettings.enable_review_step : null"
                              :class="['w-16 h-8 rounded-full relative transition-all shadow-inner', wsSettings.enable_review_step ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-700', !isOwner ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']"
                          >
                              <div :class="['absolute top-1 left-1 w-6 h-6 rounded-full bg-white transition-all shadow-md', wsSettings.enable_review_step ? 'translate-x-8' : '']"></div>
                          </div>
                        </div>

                        <div v-if="!isOwner" class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/20 rounded-2xl flex items-center gap-3">
                           <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                           <p class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest">View Only · You must be the owner to change settings</p>
                        </div>
                        
                        <div class="flex items-center justify-between pt-6 border-t border-slate-50 dark:border-slate-800/50">
                          <div>
                              <h3 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-xs">Custom Board Workflow (Soon)</h3>
                              <p class="text-xs text-slate-400 mt-1 font-bold">Customize your kanban column names and order.</p>
                          </div>
                          <div class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-[10px] font-black text-slate-400 uppercase rounded-lg">Pro</div>
                        </div>
                    </div>
                  </div>

                  <div class="flex justify-end gap-4 mt-8">
                     <button 
                       @click="saveWorkspaceSettings" 
                       :disabled="loading || !isOwner"
                      class="px-10 py-4 bg-emerald-500 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-xs shadow-xl shadow-emerald-500/30 hover:bg-emerald-600 transition-all active:scale-95 disabled:opacity-50"
                    >
                        {{ loading ? 'UPDATING...' : 'UPDATE WORKSPACE' }}
                    </button>
                  </div>
              </div>
              <div v-else-if="!workspaceStore.loading" class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-12 text-center border border-slate-100 dark:border-slate-800 font-bold text-slate-400 animate-in fade-in duration-500">
                  <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                  </div>
                  <p class="uppercase tracking-widest text-xs">Select a workspace from the list above to view settings.</p>
              </div>
           </section>

           <!-- Integrations Tab -->
           <section v-if="activeTab === 'integrations'" class="space-y-6">
              <div class="flex items-center gap-2 mb-6">
                 <div class="w-1.5 h-6 bg-blue-500 rounded-full"></div>
                 <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-wider">Integrations</h2>
              </div>

              <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                 <div class="p-8 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                       <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center">
                          <svg class="w-6 h-6" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-1 .67-2.28 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                          </svg>
                       </div>
                       <div>
                          <h3 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-sm">Google Calendar</h3>
                          <p class="text-xs text-slate-500 mt-1 font-medium">Sync task start times as events to your primary calendar.</p>
                       </div>
                    </div>
                    
                    <div v-if="auth.user?.has_google_integration">
                        <div v-if="!auth.user?.google_calendar_scopes_granted" class="flex flex-col items-end gap-2">
                            <span class="px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-2 border border-rose-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                Action Required
                            </span>
                            <a 
                                href="/auth/google"
                                class="px-4 py-2 bg-rose-600 text-white hover:bg-rose-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-rose-500/20"
                            >
                                Reconnect
                            </a>
                        </div>
                        <span v-else class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 border border-emerald-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"/></svg>
                            Connected
                        </span>
                    </div>
                    <a 
                       v-else
                       href="/auth/google"
                       class="px-6 py-3 bg-slate-800 text-white hover:bg-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg hover:shadow-indigo-500/20"
                    >
                       Connect
                    </a>
                 </div>
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
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useWorkspaceStore } from '../stores/workspace';
import { watch } from 'vue';

const router = useRouter();
const auth = useAuthStore();
const workspaceStore = useWorkspaceStore();

const loading = ref(false);
const showToast = ref(false);
const activeTab = ref('workspace');
const selectedWorkspaceId = ref(null);

const settings = ref({
   email_enabled: true,
   types: ['task_created', 'task_updated', 'task_completed'],
   exclude_self: true,
   exclude_tasks_created_by_me: false
});

const wsSettings = ref({
   enable_review_step: true
});

const activeWorkspace = computed(() => {
   if (!Array.isArray(workspaceStore.workspaces)) return undefined;
   return workspaceStore.workspaces.find(ws => Number(ws.id) === Number(selectedWorkspaceId.value));
});

const isOwner = computed(() => {
  if (!activeWorkspace.value || !auth.user) return false;
  
  const matchesRole = (role) => {
    if (!role) return false;
    const r = String(role).toLowerCase();
    return r === 'owner';
  };

  // 1. Direct check from API-provided role (appended attribute)
  if (matchesRole(activeWorkspace.value.user_role)) return true;
  
  // 2. Check if user is the explicit creator/owner in DB
  if (String(activeWorkspace.value.owner_id) === String(auth.user.id)) return true;
  
  // 3. Check if user has owner role in the pivot data of the current selection
  if (matchesRole(activeWorkspace.value.pivot?.role)) return true;
  
  // 4. Search the members list for current user (ID or Email match)
  const member = activeWorkspace.value.members?.find(m => 
    String(m.id) === String(auth.user.id) || 
    (m.email && auth.user.email && m.email.toLowerCase() === auth.user.email.toLowerCase())
  );
  return matchesRole(member?.pivot?.role);
});

const notificationTypes = [
   { id: 'task_created', label: 'A new task is added to my workspace' },
   { id: 'task_updated', label: 'An existing task is modified' },
   { id: 'task_completed', label: 'A task is marked as complete' }
];

const isPageReady = computed(() => {
   return auth.user && workspaceStore.initialized;
});

onMounted(async () => {
   loading.value = true;
   if (!auth.initialized) await auth.fetchUser();
   // Always fetch workspaces to ensure we have the latest roles/members
   await workspaceStore.fetchWorkspaces();
   syncLocalState();
   loading.value = false;
});

const syncLocalState = () => {
   if (workspaceStore.currentWorkspace) {
      selectedWorkspaceId.value = workspaceStore.currentWorkspace.id;
   } else if (workspaceStore.workspaces.length > 0) {
      selectedWorkspaceId.value = workspaceStore.workspaces[0].id;
   }

   if (auth.user?.notification_settings) {
      settings.value = { ...settings.value, ...auth.user.notification_settings };
   }

   loadWorkspaceSettings();
};

watch(() => workspaceStore.initialized, (isInit) => {
   if (isInit) syncLocalState();
});

// Watch activeWorkspace specifically to reload sub-settings when data objects change
// Watch specific properties to reload settings without deep overhead
watch(() => activeWorkspace.value?.settings, (newVal) => {
   loadWorkspaceSettings();
}, { deep: true });

const loadWorkspaceSettings = () => {
   if (activeWorkspace.value) {
      const settings = activeWorkspace.value.settings || {};
      wsSettings.value = { 
         enable_review_step: settings.enable_review_step !== false 
      };
   } else {
      wsSettings.value = { enable_review_step: true };
   }
};

watch(selectedWorkspaceId, () => {
   loadWorkspaceSettings();
});

const toggleType = (id) => {
   const index = settings.value.types.indexOf(id);
   if (index > -1) {
      settings.value.types.splice(index, 1);
   } else {
      settings.value.types.push(id);
   }
};

const saveUserSettings = async () => {
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

const saveWorkspaceSettings = async () => {
   if (!selectedWorkspaceId.value) return;
   loading.value = true;
   try {
      await workspaceStore.updateWorkspaceSettings(selectedWorkspaceId.value, wsSettings.value);
      showToast.value = true;
      
      // SPA Navigation: Go back to dashboard after showing success
      setTimeout(() => {
         router.push('/dashboard');
      }, 1500);
   } catch (error) {
      console.error('Failed to save workspace settings', error);
      alert('Failed to save workspace settings: ' + (error.response?.data?.message || 'Server error'));
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
