<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans selection:bg-indigo-500/30">
    <router-view></router-view>
    
    <!-- Custom UI Dialogs & Toasts -->
    <GlobalUI />
    <PWAInstallPrompt />
  </div>
</template>

<script setup>
import GlobalUI from './components/GlobalUI.vue';
import PWAInstallPrompt from './components/PWAInstallPrompt.vue';
// Main App Component
</script>

<style>
@import "../css/app.css";

:root {
  --primary: #6366f1;
  --secondary: #64748b;
}

body {
  margin: 0;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>
