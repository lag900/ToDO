<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-900 p-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-[2rem] shadow-2xl p-10 text-center border border-slate-100 dark:border-slate-800">
       <div v-if="loading">
          <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
             <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
             <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">Processing Invitation...</p>
       </div>
       
       <div v-else-if="error">
          <div class="w-16 h-16 bg-rose-50 dark:bg-rose-500/10 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6">
             <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <h2 class="text-xl font-black text-slate-800 dark:text-white mb-2">Invitation Error</h2>
          <p class="text-slate-500 mb-8 text-sm leading-relaxed">{{ error }}</p>
          
          <button v-if="!auth.user" @click="$router.push('/login')" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all">
             Login / Sign Up
          </button>
          <router-link v-else to="/dashboard" class="inline-block px-8 py-3 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">
             Go to Dashboard
          </router-link>
       </div>

       <div v-else>
          <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
             <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
           <h2 class="text-xl font-black text-slate-800 dark:text-white mb-2">Welcome!</h2>
           <p class="text-slate-500 mb-8 text-sm">You have successfully joined the workspace.</p>
           <button @click="$router.push('/dashboard')" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all">
             Continue to Dashboard
           </button>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    // Wait for auth to initialize?
    if (!auth.initialized) {
        await auth.fetchUser(); 
        // Note: fetchUser interacts with backend.
    }

    const token = route.query.token;
    if (!token) {
        error.value = 'Invalid invitation link.';
        loading.value = false;
        return;
    }

    if (!auth.user) {
        error.value = 'Please log in to accept this invitation.';
        loading.value = false;
        return;
    }

    try {
        await axios.post('/api/share/accept', { token });
        loading.value = false;
    } catch (err) {
        loading.value = false;
        error.value = err.response?.data?.message || 'Failed to accept invitation.';
    }
});
</script>
