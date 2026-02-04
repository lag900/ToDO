<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex flex-col items-center justify-center p-6 font-outfit">
    <!-- Background Accents -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10">
      <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-500/10 blur-[120px] rounded-full"></div>
      <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-emerald-500/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="w-full max-w-md">
      <div class="text-center mb-10">
        <div class="w-24 h-24 bg-indigo-600 rounded-[2rem] mx-auto mb-6 flex items-center justify-center shadow-2xl shadow-indigo-500/40 transform rotate-12">
            <span class="text-white text-4xl font-black -rotate-12">Hi!</span>
        </div>
        <h1 class="text-3xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Almost there!</h1>
        <p class="text-slate-500 dark:text-slate-400 font-medium">How should we call you in THINKER?</p>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-10 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 dark:shadow-none">
        <form @submit.prevent="submit">
          <div class="mb-8">
            <label for="display-name" class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3 px-1">Display Name</label>
            <input 
              id="display-name"
              v-model="displayName" 
              type="text" 
              placeholder="e.g. Alex Thorne" 
              required
              class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 rounded-2xl px-6 py-4 outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-bold text-lg"
            >
            <p class="text-slate-400 text-[10px] mt-3 font-medium px-1 italic">This is how your colleagues will see you.</p>
          </div>

          <button 
            type="submit" 
            :disabled="loading || !displayName"
            class="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black shadow-xl shadow-indigo-500/30 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3"
          >
            <span>START WORKING</span>
            <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            <div v-else class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const displayName = ref(auth.user?.name || '');
const loading = ref(false);

const submit = async () => {
  loading.value = true;
  try {
    await auth.completeOnboarding(displayName.value);
    router.push('/');
  } catch (error) {
    alert('Oops! Something went wrong. Please try again.');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
</style>
