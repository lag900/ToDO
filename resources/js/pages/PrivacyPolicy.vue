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
        <h1 class="text-4xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Privacy Policy</h1>
        <p class="text-slate-500 dark:text-slate-400 mb-8 font-medium">Last Updated: February 4, 2026</p>

        <div class="prose prose-slate dark:prose-invert max-w-none space-y-8 text-slate-600 dark:text-slate-300">
          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Introduction</h2>
            <p>
              Welcome to <strong>Todo Batucore</strong> (https://todo.batucore.website). We value your privacy and are committed to being transparent about how we handle your information. This Privacy Policy explains what data we collect, why we collect it, and how we protect it.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Information We Collect</h2>
            <p>To provide our service, we collect the following information:</p>
            <ul class="list-disc pl-5 space-y-2">
              <li><strong>Google Account Information:</strong> When you sign in using Google OAuth, we access your name, email address, and profile picture.</li>
              <li><strong>Application Data:</strong> We store the tasks, notes, and todo lists that you create within the app to ensure they are available to you whenever you log in.</li>
            </ul>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">How We Use Information</h2>
            <p>We use your data strictly for the following purposes:</p>
            <ul class="list-disc pl-5 space-y-2">
              <li><strong>Authentication:</strong> To identify you and allow you to securely log into your account.</li>
              <li><strong>Functionality:</strong> To save and display your tasks.</li>
              <li><strong>Communication:</strong> We may use your email to send important service-related updates (not for marketing).</li>
            </ul>
            <p class="mt-4">We <strong>do not</strong> use your data for advertising purposes or to build profiles for tracking.</p>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Data Sharing</h2>
            <p>We believe your data belongs to you.</p>
            <ul class="list-disc pl-5 space-y-2">
              <li><strong>No Selling:</strong> We do not sell, trade, or rent your personal information to third parties.</li>
              <li><strong>No Sharing:</strong> We do not share your data with any third-party services, except for Google (to facilitate login) and our essential hosting infrastructure.</li>
            </ul>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Data Security</h2>
            <p>
              We use industry-standard security measures to protect your information from unauthorized access, loss, or alteration. However, please remember that no method of transmission over the internet is 100% secure.
            </p>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">User Rights</h2>
            <p>You have the right to:</p>
            <ul class="list-disc pl-5 space-y-2">
              <li>Access the information we hold about you.</li>
              <li>Request the deletion of your account and all associated data at any time through the application settings or by contacting us.</li>
            </ul>
          </section>

          <section>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">Contact Information</h2>
            <p>
              If you have any questions about this Privacy Policy, please contact us at: 
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
