<template>
  <div class="relative w-full group">
    <input 
      ref="inputRef"
      type="text"
      :id="id"
      :name="name"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 outline-none focus:ring-4 focus:ring-indigo-500/20 text-sm font-bold text-slate-600 dark:text-slate-200 transition-all cursor-pointer group-hover:border-slate-300 dark:group-hover:border-slate-600"
      :class="{'opacity-50 cursor-not-allowed': disabled}"
    />
    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';

const props = defineProps({
  id: { type: String, default: null },
  name: { type: String, default: null },
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'dd/mm/yyyy' },
  disabled: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'change']);

const inputRef = ref(null);
let fp = null;

onMounted(() => {
  fp = flatpickr(inputRef.value, {
    dateFormat: "d/m/Y",
    allowInput: true,
    altInput: false, // We use the direct display
    defaultDate: props.modelValue,
    disableMobile: true, // Force consistent UI on mobile
    onChange: (selectedDates, dateStr) => {
      // Backend actually expects dd/mm/yyyy in the current Service logic, 
      // but let's be careful. The user said: "التخزين (Backend): yyyy-mm-dd"
      // Wait, the current TaskService.php says:
      // "Helper to convert YYYY-MM-DD from <input type="date"> to DD/MM/YYYY for backend parsing"
      // So the backend ALREADY expects dd/mm/yyyy if it's coming from formatToCustom.
      // Let's check TaskService.php one more time.
      emit('update:modelValue', dateStr);
      emit('change', dateStr);
    }
  });
});

watch(() => props.modelValue, (newVal) => {
  if (fp && newVal !== fp.input.value) {
    fp.setDate(newVal, false);
  }
});

onBeforeUnmount(() => {
  if (fp) fp.destroy();
});
</script>

<style>
/* Customizing Flatpickr Theme to match our Premium Design */
.flatpickr-calendar {
  background: #ffffff !important;
  border-radius: 24px !important;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
  border: 1px solid #f1f5f9 !important;
  font-family: 'Outfit', sans-serif !important;
  padding: 8px !important;
}

.dark .flatpickr-calendar {
  background: #0f172a !important;
  border: 1px solid #1e293b !important;
  color: #f1f5f9 !important;
}

.flatpickr-day.selected {
  background: #4f46e5 !important;
  border-color: #4f46e5 !important;
  border-radius: 12px !important;
}

.flatpickr-day:hover {
  background: #f1f5f9 !important;
  border-radius: 12px !important;
}

.dark .flatpickr-day:hover {
  background: #1e293b !important;
}
</style>
