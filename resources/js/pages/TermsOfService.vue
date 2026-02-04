<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 p-6 md:p-12 font-outfit">
    <!-- Background Accents -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10">
      <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/10 blur-[120px] rounded-full"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-fuchsia-500/10 blur-[120px] rounded-full"></div>
    </div>

    <div class="max-w-3xl mx-auto">
      <div class="mb-10 flex items-center justify-between">
        <router-link to="/login" class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors font-medium">
          <ArrowLeftIcon class="w-5 h-5" />
          Back to Login
        </router-link>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 md:p-12 rounded-[2.5rem] shadow-xl">
        <h1 class="text-4xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Terms of Service</h1>
        <p class="text-slate-500 dark:text-slate-400 mb-8 font-medium">Last Updated: February 4, 2026</p>

        <div class="prose prose-slate dark:prose-invert max-w-none space-y-8 text-slate-600 dark:text-slate-300">
          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Acceptance of Terms</h2>
            <p>
              By accessing or using <strong>Todo App</strong> (https://todo.batucore.website), you agree to be bound by these simple Terms of Service. If you do not agree, please do not use the service.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Use of the Service</h2>
            <p>
              Todo App is designed to help you manage your personal tasks and todos. You are granted a limited, non-exclusive right to use the application for your own personal or professional organization.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">User Responsibilities</h2>
            <ul class="list-disc pl-5 space-y-2">
              <li><strong>Account Security:</strong> Since you log in via Google, you are responsible for maintaining the security of your Google account.</li>
              <li><strong>Lawful Use:</strong> You agree not to use the service for any illegal activities or to interrupt the service for others.</li>
              <li><strong>Content:</strong> You are responsible for the tasks and data you input into the app.</li>
            </ul>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Disclaimer</h2>
            <p>
              Todo App is provided on an <strong>"as-is"</strong> and <strong>"as-available"</strong> basis. While we strive for 100% uptime and data integrity, we do not guarantee that the service will be error-free or uninterrupted. Use of the service is at your own risk.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Limitation of Liability</h2>
            <p>
              To the maximum extent permitted by law, Todo App and its creators shall not be liable for any direct, indirect, or incidental damages resulting from your use of the service, including but not limited to loss of data or loss of productivity.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Changes to the Terms</h2>
            <p>
              We may update these terms from time to time to reflect changes in our app or for legal reasons. We will notify you of any significant changes by updating the "Last Updated" date at the top of this page. Your continued use of the app after changes are made constitutes your acceptance of the new terms.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Contact</h2>
            <p>
              Questions? Reach out to us at: 
              <a href="mailto:abdozero2030@gmail.com" class="text-indigo-600 font-bold hover:underline">abdozero2030@gmail.com</a>
            </p>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
</style>
