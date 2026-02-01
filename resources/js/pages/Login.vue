<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex flex-col items-center justify-center p-6 font-outfit">
    <!-- Background Accents -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10">
      <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/10 blur-[120px] rounded-full"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-fuchsia-500/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="w-full max-w-md">
      <div class="text-center mb-10">
        <img src="https://i.postimg.cc/jqQRtc95/thinker-(1).png" alt="THINKER Logo" class="h-24 w-auto mx-auto mb-6">
        <h1 class="text-3xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Welcome to THINKER</h1>
        <p class="text-slate-500 dark:text-slate-400 font-medium">Your enterprise workflow, simplified.</p>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-10 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 dark:shadow-none">
        <div class="mb-8">
          <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Sign In</h2>
          <p class="text-sm text-slate-500">Fast, secure access to your workspace</p>
        </div>

        <a href="/auth/google" 
           class="flex items-center justify-center gap-4 w-full py-4 px-6 bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 rounded-2xl transition-all duration-300 group shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 active:scale-[0.98]">
          <svg class="w-6 h-6" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-1 .67-2.28 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          <span class="font-bold text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 transition-colors">Continue with Google</span>
        </a>

        <div class="mt-8 pt-8 border-t border-slate-50 dark:border-slate-800 text-center">
          <p class="text-xs text-slate-400 font-medium">By continuing, you agree to our Terms of Service</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Login logic is handled by the regular link to /auth/google
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
</style>
