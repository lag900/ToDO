<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-outfit overflow-x-hidden">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800">
      <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-3 group">
          <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:rotate-12 transition-transform">
             <span class="text-white font-black text-xl">T</span>
          </div>
          <span class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Todo Batucore</span>
        </router-link>
        
        <div class="flex items-center gap-4">
          <router-link to="/login" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-95">
            Get Started
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <main class="pt-32 pb-20 px-6">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
        <div>
          <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-full text-indigo-600 dark:text-indigo-400 text-sm font-bold mb-6">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
            </span>
            v1.0 is Live!
          </div>
          <h1 class="text-5xl lg:text-7xl font-black text-slate-900 dark:text-white mb-6 leading-tight tracking-tight">
            The smartest way to <span class="text-indigo-600">organize</span> your work.
          </h1>
          <p class="text-xl text-slate-600 dark:text-slate-400 mb-10 leading-relaxed max-w-xl">
            Todo Batucore helps you manage personal tasks, team projects, and deadlines in one beautiful interface.
          </p>
          <div class="flex flex-wrap gap-4">
            <router-link to="/login" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black shadow-xl shadow-indigo-500/30 transition-all text-lg active:scale-95">
              Start Building Today
            </router-link>
            <a href="#features" class="px-8 py-4 bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 text-slate-700 dark:text-slate-200 rounded-2xl font-black transition-all text-lg">
              Explore Features
            </a>
          </div>
        </div>

        <div class="relative">
          <div class="absolute inset-0 bg-indigo-500/20 blur-[120px] rounded-full -z-10"></div>
          <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 p-4 shadow-2xl">
            <img src="https://i.postimg.cc/jqQRtc95/thinker-(1).png" alt="Todo Batucore Preview" class="rounded-[2rem] w-full h-auto opacity-20">
            <div class="absolute inset-0 flex items-center justify-center">
               <span class="text-6xl font-black text-indigo-600/20 -rotate-12">THINKER</span>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white dark:bg-slate-900/50">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-3xl mx-auto mb-20">
          <h2 class="text-3xl lg:text-5xl font-black text-slate-900 dark:text-white mb-6 tracking-tight">Built for modern workflows</h2>
          <p class="text-lg text-slate-600 dark:text-slate-400">Everything you need to stay productive, whether you're working solo or with a growing team.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <!-- Feature 1 -->
          <!-- Removed Google Calendar Feature Block -->
          <!-- Feature 2 -->
          <div class="p-8 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-700 hover:border-indigo-500 transition-all group">
            <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-2xl flex items-center justify-center shadow-lg mb-6 group-hover:scale-110 transition-transform">
              <ClipboardDocumentCheckIcon class="w-8 h-8 text-indigo-600" />
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Task Management</h3>
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Organize tasks into boards, lists, and projects with an intuitive drag-and-drop interface.</p>
          </div>
          <!-- Feature 3 -->
          <div class="p-8 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-700 hover:border-indigo-500 transition-all group">
            <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-2xl flex items-center justify-center shadow-lg mb-6 group-hover:scale-110 transition-transform">
              <UserGroupIcon class="w-8 h-8 text-indigo-600" />
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Team Collaboration</h3>
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">Invite team members to your workspace and collaborate on tasks in real-time.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-20 border-t border-slate-200 dark:border-slate-800">
      <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-10">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
             <span class="text-white font-black text-sm">T</span>
          </div>
          <span class="text-lg font-black text-slate-800 dark:text-white tracking-tight">Todo Batucore</span>
        </div>
        
        <div class="flex flex-wrap justify-center gap-8">
          <router-link to="/privacy-policy" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors uppercase tracking-widest">Privacy Policy</router-link>
          <router-link to="/terms-of-service" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors uppercase tracking-widest">Terms of Service</router-link>
          <a href="mailto:abdozero2030@gmail.com" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors uppercase tracking-widest">Contact</a>
        </div>
        
        <p class="text-sm text-slate-500 font-medium">© 2026 Todo Batucore. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { CalendarIcon, ClipboardDocumentCheckIcon, UserGroupIcon } from '@heroicons/vue/24/outline';
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
</style>
