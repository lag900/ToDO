<template>
  <div class="min-h-screen flex items-center justify-center font-outfit">
    <div class="text-center">
      <p>Redirecting to the official Privacy Policy…</p>
      <p class="mt-4 text-sm text-slate-500">If you are not redirected automatically, <a :href="canonical" class="text-indigo-600 hover:underline">click here</a>.</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const canonical = ref('https://todo.batucore.website/privacy-policy');

onMounted(() => {
  // Always redirect SPA route to the canonical, server-rendered privacy page.
  window.location.replace(canonical.value);
});
</script>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
</style>
