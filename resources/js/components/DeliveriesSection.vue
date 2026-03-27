<template>
  <div class="space-y-6">
    <!-- Section Header -->
    <div class="flex items-center justify-between">
      <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Deliveries / Attachments</h3>
      <button 
        v-if="canAdd"
        @click="showAddPicker = true"
        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all font-bold text-[10px] uppercase tracking-widest active:scale-95"
      >
        <PlusIcon class="w-3.5 h-3.5" />
        Add Attachment
      </button>
    </div>

    <!-- Attachments List -->
    <div v-if="items.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <AttachmentCard 
        v-for="item in items" :key="item.id"
        :item="item"
        :can-remove="canAdd"
        :loading="item.status === 'syncing'"
        @remove="$emit('remove', item.delivery_id || item.id)"
        @retry="$emit('retry', item)"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="py-12 flex flex-col items-center justify-center text-center bg-slate-50/50 dark:bg-slate-800/30 rounded-[2.5rem] border-2 border-dashed border-slate-100 dark:border-slate-800">
       <div class="w-16 h-16 rounded-[2rem] bg-white dark:bg-slate-900 shadow-sm flex items-center justify-center text-slate-200 mb-4 border border-slate-50 dark:border-slate-800">
          <PackageIcon class="w-8 h-8" />
       </div>
       <p class="text-xs font-black uppercase tracking-widest text-slate-400">No attachments yet</p>
       <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-tight">Upload files or links to deliver this task</p>
    </div>

    <!-- Add Picker Picker -->
    <AddAttachmentSheet 
      v-if="showAddPicker"
      :show="showAddPicker"
      @close="showAddPicker = false"
      @attached="onAttached"
    />
  </div>
</template>


<script setup>
import { ref } from 'vue';
import { 
  Plus as PlusIcon,
  Package as PackageIcon,
} from 'lucide-vue-next';
import AddAttachmentSheet from './AddAttachmentSheet.vue';
import AttachmentCard from './AttachmentCard.vue';

const props = defineProps({
  items: { type: Array, default: () => [] },
  canAdd: { type: Boolean, default: true }
});

const emit = defineEmits(['add', 'remove', 'retry']);

const showAddPicker = ref(false);

const onAttached = (newItems) => {
  emit('add', newItems);
  showAddPicker.value = false;
};
</script>

