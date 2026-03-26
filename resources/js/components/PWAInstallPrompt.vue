<template>
  <transition name="slide-up">
    <div 
      v-if="isVisible" 
      class="fixed bottom-24 left-4 right-4 sm:left-auto sm:right-8 sm:w-96 bg-white dark:bg-slate-900 rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.2)] p-6 border border-slate-100 dark:border-slate-800 z-[100000] overflow-hidden group"
    >
      <!-- Background Glow -->
      <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl group-hover:bg-indigo-500/20 transition-all duration-700"></div>

      <div class="relative flex items-start gap-4 mb-6">
        <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shrink-0 shadow-xl shadow-indigo-500/20 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
        </div>
        
        <div class="flex-1 pt-1">
          <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight leading-none">Install Batucore</h3>
          <p class="text-[11px] font-bold text-slate-500 mt-2 leading-relaxed uppercase tracking-widest">Faster access • Offline Mode</p>
        </div>

        <button @click="dismissPrompt" class="p-2 text-slate-300 hover:text-slate-500 transition-colors">
          <XIcon class="w-5 h-5" />
        </button>
      </div>

      <!-- Android/Chrome Install Action -->
      <div v-if="platform === 'android'" class="space-y-4">
        <p class="text-xs font-medium text-slate-600 dark:text-slate-400">Enjoy a desktop-quality experience directly from your home screen.</p>
        <button 
          @click="installAndroid" 
          class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/30 hover:bg-indigo-700 transition-all active:scale-95 flex items-center justify-center gap-2"
        >
          Add to Home Screen
        </button>
      </div>

      <!-- iOS Safari Instructions -->
      <div v-if="platform === 'ios'" class="space-y-4 animate-in fade-in slide-in-from-bottom-2">
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl space-y-3 border border-slate-100 dark:border-slate-800">
           <div class="flex items-center gap-3">
              <div class="w-6 h-6 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm text-xs font-black">1</div>
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">Tap the <span class="text-indigo-600 font-black inline-flex items-center gap-1 mx-1"><ShareIcon class="w-3 h-3"/> Share</span> button</p>
           </div>
           <div class="flex items-center gap-3">
              <div class="w-6 h-6 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm text-xs font-black">2</div>
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">Scroll down to <span class="text-indigo-600 font-black mx-1">"Add to Home Screen"</span></p>
           </div>
        </div>
        <button 
          @click="dismissPrompt" 
          class="w-full py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] transition-all active:scale-95"
        >
          Got it
        </button>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { X as XIcon, Share as ShareIcon } from 'lucide-vue-next';

const platform = ref(null); // 'android' or 'ios'
const showPrompt = ref(false);
const deferredPrompt = ref(null);

const isVisible = computed(() => {
  if (!showPrompt.value) return false;
  if (localStorage.getItem('pwa_prompt_dismissed')) return false;
  return true;
});

const checkPlatform = () => {
  const userAgent = window.navigator.userAgent || window.navigator.vendor || window.opera;
  
  // Detect iOS
  if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
    platform.value = 'ios';
    return;
  }

  // Detect Android or others that support beforeinstallprompt
  if (/android/i.test(userAgent)) {
    platform.value = 'android';
    return;
  }
  
  // Minimal Desktop Check (disable by default for non-mobile)
  const isMobile = window.innerWidth <= 768;
  if (!isMobile) {
    platform.value = null;
    return;
  }

  platform.value = 'android'; // Fallback for other mobile browsers
};

const isStandalone = () => {
  const isStandaloneMode = window.matchMedia('(display-mode: standalone)').matches;
  const isIOSStandalone = window.navigator.standalone === true;
  return isStandaloneMode || isIOSStandalone;
};

const handleCanInstall = () => {
  if (isStandalone()) return;
  if (localStorage.getItem('pwa_prompt_dismissed')) return;
  
  // For Android/Chrome
  if (window.deferredPrompt) {
    deferredPrompt.value = window.deferredPrompt;
    showPrompt.value = true;
  }
};

const installAndroid = async () => {
  if (!deferredPrompt.value) return;
  
  deferredPrompt.value.prompt();
  const { outcome } = await deferredPrompt.value.userChoice;
  
  if (outcome === 'accepted') {
    localStorage.setItem('pwa_prompt_dismissed', 'true'); // Implicitly dismissed as installed
    showPrompt.value = false;
  }
  
  deferredPrompt.value = null;
  window.deferredPrompt = null;
};

const dismissPrompt = () => {
  localStorage.setItem('pwa_prompt_dismissed', 'true');
  showPrompt.value = false;
};

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const isTestMode = urlParams.get('pwa-test') === 'true';

  checkPlatform();

  // For testing purposes
  if (isTestMode) {
    platform.value = platform.value || 'android';
    showPrompt.value = true;
    localStorage.removeItem('pwa_prompt_dismissed'); // Reset for test
    return;
  }

  // If already standalone, we're done
  if (isStandalone()) return;

  // iOS Logic: Show immediately if not dismissed and on mobile
  if (platform.value === 'ios') {
    if (!localStorage.getItem('pwa_prompt_dismissed')) {
      setTimeout(() => {
        showPrompt.value = true;
      }, 3000); // Wait 3 seconds to be non-intrusive
    }
  }

  // Android Logic: Wait for the event
  window.addEventListener('pwa-can-install', handleCanInstall);
  
  // Immediate check if prompt was already captured in blade
  if (window.deferredPrompt) {
    handleCanInstall();
  }
});

onUnmounted(() => {
  window.removeEventListener('pwa-can-install', handleCanInstall);
});
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active {
  transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(120%) scale(0.9);
  opacity: 0;
}
</style>
