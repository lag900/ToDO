<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-outfit overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800">
      <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-3 group">
          <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
             <span class="text-white font-black text-xl">T</span>
          </div>
          <span class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Todo Batucore</span>
        </router-link>

        <router-link to="/login" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg">
          Get Started
        </router-link>
      </div>
    </nav>

    <!-- Hero -->
    <main class="pt-32 pb-20 px-6">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">

        <div>
          <h2 class="text-2xl font-bold text-indigo-600 mb-4">
            Task management platform with Google Calendar sync
          </h2>

          <h1 class="text-5xl lg:text-7xl font-black text-slate-900 dark:text-white mb-6 leading-tight">
            The smartest way to organize your work.
          </h1>

          <p class="text-xl text-slate-600 dark:text-slate-400 mb-10 leading-relaxed">
            Todo Batucore helps you manage personal tasks, team projects,
            and deadlines in one beautiful interface.
          </p>

          <div class="flex flex-wrap gap-4">
            <router-link to="/login" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-xl">
              Start Building Today
            </router-link>
          </div>
        </div>

        <div class="relative">
          <div class="bg-white dark:bg-slate-900 rounded-[2rem] border p-4 shadow-2xl">
            <img src="https://i.postimg.cc/jqQRtc95/thinker-(1).png" class="rounded-[1.5rem] w-full opacity-20">
          </div>
        </div>

      </div>
    </main>

    <!-- ABOUT (مهم لجوجل) -->
    <section class="py-16 bg-white dark:bg-slate-950 border-y">
      <div class="max-w-4xl mx-auto px-6 text-center">
        <h2 class="text-3xl font-black text-slate-900 dark:text-white mb-6">
          About Todo Batucore
        </h2>

        <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
          Todo Batucore is an independent task and productivity web app that helps you manage tasks and optionally sync them to your Google Calendar.
        </p>

        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">
          Google Calendar integration
        </h3>

        <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
          When you enable Google Calendar, we create/update calendar events for tasks using Google's calendar.events scope. We only store the calendar event ID for synchronization.
        </p>

        <p class="text-sm font-medium">
          Todo Batucore is not affiliated with or endorsed by Google.
        </p>

        <p>
          <a href="/privacy-policy" class="text-indigo-600 hover:underline">Privacy Policy</a> ·
          <a href="/terms" class="text-indigo-600 hover:underline">Terms</a> ·
          <a href="mailto:abdozero2030@gmail.com" class="text-indigo-600 hover:underline">abdozero2030@gmail.com</a>
        </p>
      </div>
    </section>

    <!-- GOOGLE DATA USAGE (مهم للموافقة) -->
    <section class="py-20 bg-indigo-50 dark:bg-slate-900">
      <div class="max-w-5xl mx-auto px-6 text-center">

        <h2 class="text-3xl font-black mb-6 text-slate-900 dark:text-white">
          Google Data Usage & Privacy
        </h2>

        <div class="bg-white dark:bg-slate-800 rounded-3xl p-10 shadow-xl text-left space-y-4">

          <p><strong>Authentication:</strong> We use Google OAuth only to sign you into your account securely.</p>

          <p><strong>Calendar Access:</strong> If you enable Google Calendar sync, Todo Batucore creates and manages events only for your tasks.</p>

          <p><strong>Data Storage:</strong> We store only the Google Event ID required for synchronization.</p>

          <p><strong>No Data Selling:</strong> We never sell, share, or transfer Google user data to third parties.</p>

          <p><strong>User Control:</strong> You can disconnect Google Calendar anytime from settings.</p>

        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-20 border-t bg-white dark:bg-slate-950">
      <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-10">

        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
             <span class="text-white font-black text-sm">T</span>
          </div>
          <span class="text-lg font-black text-slate-800 dark:text-white">Todo Batucore</span>
        </div>

        <div class="flex gap-8">
          <router-link to="/privacy-policy" class="text-sm font-bold text-slate-500 hover:text-indigo-600">Privacy Policy</router-link>
          <router-link to="/terms-of-service" class="text-sm font-bold text-slate-500 hover:text-indigo-600">Terms</router-link>
          <a href="mailto:abdozero2030@gmail.com" class="text-sm font-bold text-slate-500 hover:text-indigo-600">Contact</a>
        </div>

        <p class="text-sm text-slate-500">© 2026 Todo Batucore</p>
      </div>

      <p class="text-center text-xs text-slate-400 mt-6">
        Todo Batucore is an independent productivity web application and is not affiliated with Google.
      </p>

    </footer>

  </div>
</template>

<script setup>
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
</style>
