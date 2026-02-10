<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 p-6 md:p-12 font-outfit">
    <div class="max-w-3xl mx-auto">
      <div class="mb-10 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors font-medium">
          <!-- ...existing icon if desired... -->
          Back to Home
        </router-link>
      </div>

      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 md:p-12 rounded-[2.5rem] shadow-xl">
        <h1 class="text-4xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Privacy Policy</h1>
        <p class="text-slate-500 dark:text-slate-400 mb-8 font-medium">Last Updated: February 4, 2026</p>

        <div class="prose prose-slate dark:prose-invert max-w-none space-y-8 text-slate-600 dark:text-slate-300">
          <section>
            <h2>Introduction</h2>
            <p>
              Todo Batucore (https://todo.batucore.website) is an independent task and productivity web application.
              This is the single, official privacy policy for Todo Batucore and is publicly accessible at
              https://todo.batucore.website/privacy-policy. It explains what data we collect, how we use it, retention
              and deletion practices, and how you can contact us.
            </p>
          </section>

          <section>
            <h2>Google OAuth data usage</h2>
            <p>
              When you sign in with Google, Todo Batucore requests only the basic identity scopes needed to authenticate:
              openid, email, and profile. We use this information (name, email, profile picture) only to create and manage
              your account and personalize the app. We do not use this data for advertising or profiling.
            </p>
            <p>
              The only additional Google API scope we request is:
              <code>https://www.googleapis.com/auth/calendar.events</code>
              — solely to create and manage calendar events that correspond to your tasks when you opt into calendar sync.
            </p>
          </section>

          <section>
            <h2>Google Calendar data usage</h2>
            <p>
              If you enable Google Calendar integration, Todo Batucore will create, update, and delete events in your Google
              Calendar corresponding to tasks you choose to sync. We use only the Calendar Events scope to perform these
              operations on your behalf. We do not read or retain full calendar contents; we only store the calendar event ID
              needed to maintain synchronization between a task and its calendar event.
            </p>
          </section>

          <section>
            <h2>Data retention policy</h2>
            <p>
              We retain your account profile information for as long as your account exists. Calendar event identifiers
              (event_id) are retained only to maintain synchronization while your account is active.
            </p>
          </section>

          <section>
            <h2>Google User Data Retention and Deletion</h2>
            <p>
              Explicit rules for Google-related data:
            </p>
            <ul>
              <li>Only the Google Calendar event_id is stored on our servers for synchronization; full event content is not stored.</li>
              <li>When you disconnect your Google account or delete your Todo Batucore account, we permanently delete all Google-related data (linked profile info, event_id mappings, and tokens) from our production systems within 30 days.</li>
              <li>You may request immediate deletion of your Google data at any time by contacting us (see Contact Information).</li>
            </ul>
          </section>

          <section>
            <h2>Data deletion policy</h2>
            <p>
              After account deletion or explicit deletion requests we purge associated data (including Google links and event_id mappings)
              within 30 days. Any retained backups containing that data are removed according to our backup retention schedule.
            </p>
          </section>

          <section>
            <h2>Limited Use and Google API Services User Data Policy</h2>
            <p>
              Our use of Google APIs follows the Google API Services User Data Policy and the Limited Use requirements.
              We only access and store Google user data to provide core application functionality (authentication and optional calendar sync).
              We do not use Google data for advertising, profiling, or transfer to unapproved third parties.
            </p>
          </section>

          <section>
            <h2> or sharing</h2>
            <p>
              We do not sell, trade, or rent your personal information. We do not share Google user data with third parties except to the extent
              necessary to provide the service (for example: Google for OAuth) or for essential hosting/operational services under confidentiality.
            </p>
          </section>

          <section>
            <h2>User revoke access info</h2>
            <p>
              You can revoke Todo Batucore's access to your Google account at any time via your Google Account permissions:
              https://myaccount.google.com/permissions
            </p>
          </section>

          <section>
            <h2>Security</h2>
            <p>
              We use industry-standard measures to protect your information in transit and at rest. OAuth tokens are stored encrypted
              and we do not log tokens in plaintext. Refresh tokens are stored encrypted using the application's encryption helpers.
            </p>
          </section>

          <section>
            <h2>Contact Information</h2>
            <p>
              For questions, concerns, or data deletion requests contact: <a href="mailto:abdozero2030@gmail.com">abdozero2030@gmail.com</a>
            </p>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// NOTE: Ensure your router/server exposes /privacy-policy publicly (requiresAuth = false).
import { onMounted } from 'vue';

onMounted(() => {
  // Ensure SPA route redirects to the canonical server-rendered privacy page
  if (window.location.pathname !== '/privacy-policy') {
    window.location.replace('/privacy-policy');
  }
});
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
</style>
