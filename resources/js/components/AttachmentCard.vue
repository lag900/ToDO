<template>
  <div 
    class="group relative flex items-center gap-4 p-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-[2rem] hover:border-indigo-500 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all cursor-pointer overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 outline-none"
    role="button"
    :tabindex="loading ? -1 : 0"
    :aria-label="'Open attachment: ' + item.name"
    @click="handleCardClick"
    @keydown.enter="handleCardClick"
    :class="{ 
      'opacity-80 cursor-wait': loading, 
      'border-rose-200 dark:border-rose-900/50 bg-rose-50/10 hover:border-rose-500': item.status === 'error' 
    }"
  >
    <!-- Icon / Preview Container -->
    <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden border border-slate-100 dark:border-slate-700 relative">
      <img v-if="item.type === 'image'" :src="formattedUrl" class="w-full h-full object-cover transition-transform group-hover:scale-110 duration-500">
      <component v-else :is="typeIcon" class="w-6 h-6 text-slate-400 group-hover:text-indigo-500 transition-colors" />
      
      <!-- Overlay for loading -->
      <div v-if="loading" class="absolute inset-0 bg-white/60 dark:bg-slate-900/60 flex items-center justify-center">
         <Loader2Icon class="w-5 h-5 text-indigo-600 animate-spin" />
      </div>

      <!-- Icon Overlay (Open indicator on hover) -->
      <div v-if="!loading && item.status !== 'error'" class="absolute inset-0 bg-indigo-600/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
         <div class="bg-indigo-600 text-white rounded-lg p-1 animate-in zoom-in-50 duration-200">
            <component :is="item.type === 'link' ? ExternalLinkIcon : (item.type === 'image' ? MaximizeIcon : DownloadIcon)" class="w-3 h-3" />
         </div>
      </div>
    </div>

    <!-- Info Section -->
    <div class="flex-1 min-w-0 pr-8">
      <div class="flex items-center gap-2 mb-0.5">
        <span class="text-[8px] font-black uppercase tracking-widest text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10 px-1.5 py-0.5 rounded">{{ item.type }}</span>
        <span v-if="item.platform" class="text-[8px] font-black uppercase tracking-widest text-slate-400 font-heading">{{ item.platform }}</span>
        <span v-if="loading" class="text-[8px] font-black uppercase tracking-widest text-indigo-500 animate-pulse">Syncing...</span>
        <span v-if="item.status === 'error'" class="text-[8px] font-black uppercase tracking-widest text-rose-500">Upload Failed</span>
      </div>
      <div v-if="isRenaming" class="mt-1" @click.stop>
        <input 
          v-model="tempName" 
          @keyup.enter="saveName"
          @blur="saveName"
          class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-indigo-500 rounded-xl px-3 py-1 text-[11px] font-black uppercase text-slate-800 dark:text-white outline-none"
          ref="renameInput"
          autofocus
        >
      </div>
      <p v-else class="text-[11px] font-black text-slate-800 dark:text-white truncate uppercase tracking-tight group-hover:text-indigo-600 transition-colors">
        {{ item.name }}
      </p>
    </div>

    <!-- Actions Row -->
    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
      <!-- Retry Action -->
      <button 
        v-if="item.status === 'error'"
        @click.stop="$emit('retry')"
        class="p-2 bg-rose-500 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-rose-600 transition-all shadow-lg shadow-rose-500/20 active:scale-95 z-20"
      >
        Retry
      </button>

      <!-- Rename Action -->
      <button 
        v-if="canRemove && !loading && !isRenaming"
        @click.stop="startRenaming"
        class="p-2 text-slate-300 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100 active:scale-90 z-20"
        title="Rename Attachment"
      >
        <PencilIcon class="w-4 h-4" />
      </button>

      <!-- Delete Action -->
      <button 
        v-if="canRemove && !loading && !isRenaming"
        @click.stop="$emit('remove')"
        class="p-2 text-slate-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100 active:scale-90 z-20"
        title="Remove Attachment"
      >
        <TrashIcon class="w-4 h-4" />
      </button>

      <!-- Default Link Indicator -->
      <div v-if="item.type === 'link' && !loading && !item.status?.includes('error')" class="p-2 text-slate-300 group-hover:hidden transition-all">
        <ExternalLinkIcon class="w-4 h-4" />
      </div>
    </div>

    <!-- Lightbox/Modal Portal for Images -->
    <teleport to="body">
      <transition name="fade">
        <div v-if="showLightbox" class="fixed inset-0 z-[70000] flex items-center justify-center p-4">
           <!-- Backdrop -->
           <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-xl" @click="showLightbox = false"></div>
           
           <!-- Content -->
           <div class="relative max-w-[90vw] max-h-[90vh] flex flex-col items-center animate-in zoom-in-95 duration-300">
              <img :src="formattedUrl" class="max-w-full max-h-[calc(90vh-100px)] rounded-[2rem] shadow-2xl border border-white/10 ring-1 ring-white/20 object-contain">
              
              <div class="mt-6 flex flex-col items-center gap-2">
                 <p class="text-white font-black uppercase tracking-widest text-sm">{{ item.name }}</p>
                 <div class="flex items-center gap-4 mt-2">
                   <button @click="openExternal" class="flex items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                      <ExternalLinkIcon class="w-4 h-4" /> Open Original
                   </button>
                    <button @click="showLightbox = false" class="flex items-center gap-2 px-6 py-3 bg-white text-black rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                      Close
                   </button>
                 </div>
              </div>

              <!-- Top Actions -->
              <div class="absolute -top-12 right-0 flex items-center gap-2">
                 <button @click="showLightbox = false" class="p-2 bg-white/10 hover:bg-rose-500 text-white rounded-full transition-all">
                    <XIcon class="w-6 h-6" />
                 </button>
              </div>
           </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { 
  Link as LinkIcon,
  FileText as FileIcon,
  ImageIcon,
  Trash2 as TrashIcon,
  ExternalLink as ExternalLinkIcon,
  Download as DownloadIcon,
  Maximize2 as MaximizeIcon,
  Youtube as YoutubeIcon,
  Github as GithubIcon,
  Figma as FigmaIcon,
  Loader2 as Loader2Icon,
  X as XIcon,
  Pencil as PencilIcon
} from 'lucide-vue-next';
import axios from 'axios';
import { useUIStore } from '../stores/ui';

const props = defineProps({
  item: { type: Object, required: true },
  canRemove: { type: Boolean, default: true },
  loading: { type: Boolean, default: false }
});

const emit = defineEmits(['remove', 'retry', 'updated']);

const ui = useUIStore();
const showLightbox = ref(false);
const isRenaming = ref(false);
const tempName = ref('');
const isSyncingName = ref(false);
const renameInput = ref(null);

const formattedUrl = computed(() => {
  const content = props.item.content;
  if (!content) return '';
  if (content.startsWith('http') || content.startsWith('blob:')) return content;
  // If it's a storage link, format it
  if (content.includes('/storage/deliveries/')) return content;
  return `/storage/${content}`;
});

const typeIcon = computed(() => {
  if (props.item.type === 'link') {
    const url = props.item.content.toLowerCase();
    if (url.includes('youtube.com') || url.includes('youtu.be')) return YoutubeIcon;
    if (url.includes('github.com')) return GithubIcon;
    if (url.includes('figma.com')) return FigmaIcon;
    return LinkIcon;
  }
  if (props.item.type === 'image') return ImageIcon;
  return FileIcon;
});

const handleCardClick = () => {
  if (props.loading || props.item.status === 'error') return;

  if (props.item.type === 'image') {
    showLightbox.value = true;
  } else {
    openExternal();
  }
};

const openExternal = () => {
  window.open(formattedUrl.value, '_blank');
};

const startRenaming = () => {
  tempName.value = props.item.name;
  isRenaming.value = true;
  setTimeout(() => renameInput.value?.focus(), 100);
};

const saveName = async () => {
  if (!isRenaming.value) return;
  const newName = tempName.value.trim();
  if (!newName || newName === props.item.name) {
    isRenaming.value = false;
    return;
  }

  isSyncingName.value = true;
  const originalName = props.item.name;
  try {
    // Optimistic
    props.item.name = newName;
    isRenaming.value = false;
    
    await axios.patch(`/api/delivery-items/${props.item.id}`, { name: newName });
    emit('updated');
  } catch (error) {
    props.item.name = originalName;
    ui.notify('Failed to rename', 'error');
  } finally {
    isSyncingName.value = false;
  }
};
</script>

<style scoped>
.scale-102 {
  transform: scale(1.02);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

