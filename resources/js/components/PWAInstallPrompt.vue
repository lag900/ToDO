<template>
  <teleport to="body">
    <transition name="pwa-slide">
      <div v-if="isVisible" class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[calc(100%-2rem)] max-w-sm z-[100000] animate-in slide-in-from-bottom-20 duration-500">
        <div class="glass-prompt bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl p-6 rounded-[2.5rem] shadow-2xl border border-white/20 dark:border-slate-800/50 flex flex-col gap-5">
          <!-- App Icon & Info -->
          <div class="flex items-center gap-4">
             <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/30 shrink-0">
               <svg xmlns="http://www.w3.org/2000/svg" class="text-white w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
             </div>
             <div class="flex-1">
               <h4 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">THINKER Pro</h4>
               <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-bold tracking-widest leading-tight">Install for instant access and better focus</p>
             </div>
             <button @click="dismiss" class="p-2 text-slate-300 hover:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-all active:scale-90">
               <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
             </button>
          </div>

          <!-- Platform Specific Instructions -->
          <div v-if="isIOS" class="bg-indigo-50/50 dark:bg-indigo-500/5 p-4 rounded-2xl border border-indigo-100/50 dark:border-indigo-500/10">
            <p class="text-[11px] font-bold text-indigo-900 dark:text-indigo-200 uppercase tracking-widest text-center flex items-center justify-center gap-2">
              Tap <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg> 
              Share <span class="text-indigo-400">→</span> Add to Home Screen
            </p>
          </div>

          <div v-else class="flex flex-col gap-2">
            <button @click="install" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] font-black uppercase tracking-[0.2em] text-[11px] transition-all shadow-xl shadow-indigo-500/30 active:scale-[0.98]">
              Install App
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isVisible = ref(false);
const deferredPrompt = ref(null);
const isIOS = ref(false);

const checkVisibility = () => {
    // 1. Detect if already in standalone mode
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isStandalone) return;

    // 2. Check dismissal
    const lastDismissal = localStorage.getItem('pwa_prompt_dismissed');
    if (lastDismissal) {
        const dismissalTime = new Date(parseInt(lastDismissal));
        const now = new Date();
        // Don't show again for 7 days if dismissed
        if (now - dismissalTime < 7 * 24 * 60 * 60 * 1000) return;
    }

    // 3. Detect Mobile
    const userAgent = window.navigator.userAgent.toLowerCase();
    const isMobile = /iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(userAgent);
    
    if (!isMobile) return;

    // 4. Platform check
    isIOS.value = /iphone|ipad|ipod/.test(userAgent);

    // Show after slight delay
    setTimeout(() => {
        isVisible.value = true;
    }, 3000);
};

onMounted(() => {
    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt.value = e;
    });

    checkVisibility();
});

const install = async () => {
    if (deferredPrompt.value) {
        deferredPrompt.value.prompt();
        const { outcome } = await deferredPrompt.value.userChoice;
        if (outcome === 'accepted') {
            isVisible.value = false;
        }
        deferredPrompt.value = null;
    } else {
        // Fallback or generic instructions for Android if beforeinstallprompt not available yet
        window.alert('Please use your browser menu and select "Install" or "Add to Home screen"');
    }
};

const dismiss = () => {
    isVisible.value = false;
    localStorage.setItem('pwa_prompt_dismissed', Date.now().toString());
};
</script>

<style scoped>
.glass-prompt {
  box-shadow: 
    0 20px 50px rgba(0, 0, 0, 0.3),
    inset 0 0 0 1px rgba(255, 255, 255, 0.1);
}

.pwa-slide-enter-active, .pwa-slide-leave-active {
  transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.pwa-slide-enter-from, .pwa-slide-leave-to {
  transform: translate(-50%, 150%) scale(0.9);
  opacity: 0;
}
</style>
