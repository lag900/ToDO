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
      <div 
        v-for="item in items" :key="item.id"
        class="group relative flex items-center gap-4 p-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-[2rem] hover:border-indigo-500/50 hover:shadow-xl hover:shadow-indigo-500/5 transition-all cursor-pointer overflow-hidden"
      >
        <!-- Icon / Preview Container -->
        <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden border border-slate-50 dark:border-slate-700">
          <img v-if="item.type === 'image'" :src="getProperUrl(item.content)" class="w-full h-full object-cover">
          <component v-else :is="getTypeIcon(item)" class="w-6 h-6 text-slate-400" />
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0 pr-8" @click="openItem(getProperUrl(item.content))">
          <div class="flex items-center gap-2 mb-0.5">
            <span class="text-[8px] font-black uppercase tracking-widest text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10 px-1.5 py-0.5 rounded">{{ item.type }}</span>
            <span v-if="item.platform" class="text-[8px] font-black uppercase tracking-widest text-slate-400">{{ item.platform }}</span>
          </div>
          <p class="text-[11px] font-black text-slate-800 dark:text-white truncate uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ item.name }}</p>
        </div>

        <!-- Remove Button -->
        <button 
          v-if="canAdd"
          @click.stop="$emit('remove', item.delivery_id)"
          class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-slate-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100"
        >
          <TrashIcon class="w-4 h-4" />
        </button>

        <!-- External Link Icon -->
        <div v-if="item.type === 'link'" class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-slate-300 group-hover:hidden transition-all">
          <ExternalLinkIcon class="w-4 h-4" />
        </div>
      </div>
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
import { ref, computed } from 'vue';
import { 
  Plus as PlusIcon,
  Package as PackageIcon,
  Link as LinkIcon,
  FileText as FileIcon,
  ImageIcon,
  Trash2 as TrashIcon,
  ExternalLink as ExternalLinkIcon,
  Youtube as YoutubeIcon,
  Github as GithubIcon,
  Figma as FigmaIcon,
  Globe as GlobeIcon
} from 'lucide-vue-next';
import AddAttachmentSheet from './AddAttachmentSheet.vue';

const props = defineProps({
  items: { type: Array, default: () => [] },
  canAdd: { type: Boolean, default: true }
});

const emit = defineEmits(['add', 'remove']);

const showAddPicker = ref(false);

const onAttached = (newItems) => {
  emit('add', newItems);
  showAddPicker.value = false;
};

const getProperUrl = (content) => {
  if (!content) return '';
  if (content.startsWith('http')) return content;
  // If it's a storage link, format it
  if (content.includes('/storage/deliveries/')) return content;
  return `/storage/${content}`;
};

const openItem = (url) => {
  window.open(url, '_blank');
};

const getTypeIcon = (item) => {
  if (item.type === 'link') {
    const url = item.content.toLowerCase();
    if (url.includes('youtube.com') || url.includes('youtu.be')) return YoutubeIcon;
    if (url.includes('github.com')) return GithubIcon;
    if (url.includes('figma.com')) return FigmaIcon;
    return LinkIcon;
  }
  if (item.type === 'image') return ImageIcon;
  return FileIcon;
};
</script>
