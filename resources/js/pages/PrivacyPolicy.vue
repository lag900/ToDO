<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 p-6 md:p-12 font-outfit">
    <!-- Background Accents -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10">
      <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/10 blur-[120px] rounded-full"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-fuchsia-500/10 blur-[120px] rounded-full">
      </div>
    </div>

    <div class="max-w-3xl mx-auto">
      <div class="mb-10 flex items-center justify-between">
        <router-link to="/"
          class="flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors font-medium">
          <ArrowLeftIcon class="w-5 h-5" />
          Back to Home
        </router-link>
      </div>

      <div class="bg-white dark:bg-slate-900 border p-8 md:p-12 rounded-[2.5rem] shadow-xl">
        <h1 class="text-4xl font-black mb-2 tracking-tight">Privacy Policy</h1>
        <p class="text-slate-500 mb-8 font-medium">Last Updated: February 4, 2026</p>

        <div class="prose max-w-none space-y-8 text-slate-600 dark:text-slate-300">
          <section>
            <h2>Introduction</h2>
            <p>
              Todo Batucore (https://todo.batucore.website) is an independent task management web application.
              This Privacy Policy explains what information we collect, how we use it, your choices, and how we protect
              your data. This page is the single official privacy policy for Todo Batucore and is publicly accessible.
            </p>
          </section>

          <section>
            <h2>Google OAuth data usage</h2>
            <p>
              When you sign in with Google, we request your basic profile information (name, email, profile picture)
              to create and manage your account. We use these fields solely for authentication and to personalize your
              experience.
            </p>
            <p>
              OAuth scopes requested: openid, email, profile, and https://www.googleapis.com/auth/calendar.events.
              No other Google API scopes are requested or used.
            </p>
          </section>

          <section>
            <h2>Google Calendar data usage</h2>
            <p>
              If you opt into calendar integration, Todo Batucore uses Google Calendar to create and update events that
              correspond to your tasks. We only request the calendar.events scope to create/delete/update events on your
              behalf. We do not read or store full calendar contents; only the event identifier (event_id) needed for
              synchronization is stored.
            </p>
          </section>

          <section>
            <h2>Data retention policy</h2>
            <p>
              We retain your account profile information for as long as your account exists. Calendar event identifiers
              (event_id) are retained only to keep synchronization mapping for active accounts.
            </p>
          </section>

          <section>
            <h2>Google User Data Retention and Deletion</h2>
            <p>
              Explicit retention and deletion rules for Google user data:
            </p>
            <ul>
              <li>Only the Google Calendar event_id is stored on our servers for synchronization; full event content is not stored.</li>
              <li>When you disconnect Google or delete your Todo Batucore account, all Google-related data (profile links, event_id mappings, tokens) will be removed from our systems within 30 days.</li>
              <li>You may request immediate deletion of your Google data at any time by contacting us (see Contact Information).</li>
            </ul>
          </section>

          <section>
            <h2>Data deletion policy</h2>
            <p>
              After account deletion or explicit deletion requests we purge associated data (including Google links and event_id mappings)
              within 30 days. Backups containing this data will be wiped in the next regular backup retention cycle.
            </p>
          </section>

          <section>
            <h2>No selling or sharing</h2>
            <p>
              We do not sell, trade, or rent your personal information. We do not share Google user data with third parties except to the extent necessary
              to provide the service (for example: Google to facilitate OAuth) or for essential hosting/operational services bound by confidentiality.
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
              We use industry-standard measures to protect your information at rest and in transit. OAuth tokens are stored encrypted on our servers.
              We do not log tokens in plaintext.
            </p>
          </section>

          <section>
            <h2>Google API Services User Data Policy</h2>
            <p>
              Our use of Google APIs follows the Google API Services User Data Policy (including Limited Use requirements).
              We only use Google user data to provide core application functionality and do not use it for advertising or profiling.
            </p>
          </section>

          <section>
            <h2>Contact Information</h2>
            <p>
              For questions or deletion requests contact: <a href="mailto:abdozero2030@gmail.com">abdozero2030@gmail.com</a>
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
