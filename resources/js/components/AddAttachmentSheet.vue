<template>
  <div v-if="show" class="fixed inset-0 z-[60000] flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-hidden">
    <!-- Backdrop -->
    <transition name="fade" appear>
      <div v-if="show" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="$emit('close')"></div>
    </transition>

    <!-- Modal / Bottom Sheet -->
    <transition :name="isMobile ? 'slide-up' : 'zoom-in'" appear>
      <div 
        v-if="show"
        class="relative w-full sm:max-w-md bg-white dark:bg-slate-900 rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-2xl flex flex-col z-10 overflow-hidden"
      >
        <!-- Grabbing handle for mobile -->
        <div v-if="isMobile" class="w-12 h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto mt-4 mb-2"></div>

        <!-- Header -->
        <div class="px-8 py-6 flex items-center justify-between border-b border-slate-50 dark:border-slate-800/50">
          <h2 class="text-xl font-bold font-heading text-slate-800 dark:text-white uppercase tracking-tight">Add Attachment</h2>
          <button @click="$emit('close')" class="hidden sm:block p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
            <XIcon class="w-6 h-6" />
          </button>
        </div>

        <!-- Options Container -->
        <div class="p-8 space-y-4">
          <!-- Picker View -->
          <div v-if="!activePick" class="grid grid-cols-2 gap-4 animate-in fade-in slide-in-from-bottom-2 duration-300">
            <button 
              v-for="option in options" :key="option.id"
              @click="handleOptionClick(option.id)"
              class="flex flex-col items-center justify-center gap-3 p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] border border-transparent hover:border-indigo-500/50 hover:bg-white dark:hover:bg-slate-800 transition-all group active:scale-[0.98]"
            >
              <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform', option.bg]">
                <component :is="option.icon" class="w-7 h-7 text-white" />
              </div>
              <span class="text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ option.label }}</span>
            </button>
          </div>

          <!-- Link Input View -->
          <div v-else-if="activePick === 'link'" class="space-y-5 animate-in fade-in slide-in-from-right-4 duration-300">
            <!-- Header -->
            <div class="flex items-center justify-between px-1">
              <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 font-heading">Add Link</label>
              <button @click="activePick = null" class="text-[10px] font-black uppercase tracking-widest text-indigo-600">← Back</button>
            </div>

            <!-- URL Input -->
            <div class="space-y-1">
              <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 px-1">URL</p>
              <div class="relative">
                <LinkIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300" />
                <input 
                  type="url" 
                  v-model="linkUrl" 
                  placeholder="https://..." 
                  class="w-full h-14 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl pl-12 pr-6 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-sm"
                  @input="handleLinkInput"
                  ref="linkInputRef"
                >
              </div>
            </div>

            <!-- Label Input -->
            <div class="space-y-2">
              <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 px-1">اللينك ده بتاع إيه؟</p>
              <input 
                type="text" 
                v-model="linkLabel" 
                placeholder="e.g. Design File, API Docs, Reference..."
                class="w-full h-12 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-semibold text-sm"
              >
              <!-- Quick Pick Chips -->
              <div class="flex flex-wrap gap-2 pt-1">
                <button 
                  v-for="chip in linkLabelChips" :key="chip"
                  @click="linkLabel = chip"
                  :class="[
                    'px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border',
                    linkLabel === chip 
                      ? 'bg-indigo-600 text-white border-indigo-600' 
                      : 'bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-indigo-400 hover:text-indigo-600'
                  ]"
                >{{ chip }}</button>
              </div>
            </div>

            <!-- Platform Preview -->
            <transition name="fade">
              <div v-if="linkPreview" class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                  <component :is="linkPreview.icon" class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                  <p class="text-[9px] font-black uppercase tracking-widest text-indigo-600">{{ linkPreview.platform }}</p>
                  <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300 truncate">{{ linkUrl }}</p>
                </div>
              </div>
            </transition>

            <button 
              @click="saveLink" 
              :disabled="!linkUrl"
              class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-xl shadow-indigo-500/20 hover:bg-indigo-700 transition-all uppercase tracking-widest text-sm disabled:opacity-50 active:scale-[0.98]"
            >
              Add Link
            </button>
          </div>

          <!-- Uploading state for files -->
          <div v-else-if="activePick === 'uploading'" class="py-12 flex flex-col items-center justify-center gap-6 animate-pulse">
            <div class="w-20 h-20 rounded-[2rem] bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600">
               <Loader2Icon class="w-10 h-10 animate-spin" />
            </div>
            <div class="text-center">
               <p class="text-sm font-black uppercase tracking-[0.2em] text-slate-800 dark:text-white">Uploading Artifacts</p>
               <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Processing and sanitizing...</p>
            </div>
          </div>

          <!-- Hidden file inputs (outside conditional chain so refs work correctly) -->
          <input ref="cameraInput" type="file" class="hidden" accept="image/*" capture="environment" @change="onFileChange($event, 'image')">
          <input ref="imageInput" type="file" class="hidden" accept="image/*" @change="onFileChange($event, 'image')" multiple>
          <input ref="fileInput" type="file" class="hidden" accept="*/*" @change="onFileChange($event, 'file')" multiple>
        </div>

        <!-- Footer Spacer for mobile (safe area) -->
        <div class="h-6 sm:hidden"></div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
  X as XIcon, 
  Camera as CameraIcon, 
  ImageIcon, 
  Link as LinkIcon, 
  FileText as FileIcon,
  Circle as CircleIcon,
  Youtube as YoutubeIcon,
  Github as GithubIcon,
  Figma as FigmaIcon,
  Globe as GlobeIcon,
  Loader2 as Loader2Icon
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
});

const emit = defineEmits(['close', 'attached']);

const activePick = ref(null);
const linkUrl = ref('');
const linkLabel = ref('');
const linkPreview = ref(null);
const isMobile = ref(false);
const linkInputRef = ref(null);

const linkLabelChips = [
  'Design File',
  'Google Drive',
  'Reference',
  'API Docs',
  'Figma',
  'GitHub Repo',
  'Video',
  'Report',
];

const options = [
  { id: 'camera', label: 'Take Photo', type: 'camera', icon: CameraIcon, bg: 'bg-emerald-500', accept: 'image/*', capture: 'environment' },
  { id: 'image', label: 'Upload Image', type: 'image', icon: ImageIcon, bg: 'bg-rose-500', accept: 'image/*' },
  { id: 'link', label: 'Add Link', type: 'link', icon: LinkIcon, bg: 'bg-indigo-600' },
  { id: 'file', label: 'Upload File', type: 'file', icon: FileIcon, bg: 'bg-slate-600', accept: '*/*' }
];

const checkMobile = () => {
  isMobile.value = window.innerWidth < 640;
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});

const handleOptionClick = (id) => {
  if (id === 'link') {
    activePick.value = 'link';
    setTimeout(() => linkInputRef.value?.focus(), 100);
  } else {
    if (id === 'camera' && cameraInput.value) cameraInput.value.click();
    if (id === 'image' && imageInput.value) imageInput.value.click();
    if (id === 'file' && fileInput.value) fileInput.value.click();
  }
};

// Simplified ref handling for inputs
const cameraInput = ref(null);
const imageInput = ref(null);
const fileInput = ref(null);

const onFileChange = async (event, type) => {
  const files = event.target.files;
  if (!files.length) return;

  activePick.value = 'uploading';
  const newItems = [];

  for (const file of files) {
    const formData = new FormData();
    formData.append('file', file);
    try {
      const res = await axios.post('/api/delivery-upload', formData);
      newItems.push({
        type: file.type.startsWith('image/') ? 'image' : 'file',
        name: file.name,
        content: res.data.path
      });
    } catch (e) {
      console.error('Upload failed', e);
    }
  }

  if (newItems.length > 0) {
    emit('attached', newItems);
  }
  
  // Reset input values so the same file can be selected again
  if (cameraInput.value) cameraInput.value.value = '';
  if (imageInput.value) imageInput.value.value = '';
  if (fileInput.value) fileInput.value.value = '';
  
  resetStore();
};

const handleLinkInput = () => {
  const url = linkUrl.value.toLowerCase();
  if (!url) {
    linkPreview.value = null;
    return;
  }

  let platform = 'Website';
  let icon = GlobeIcon;

  if (url.includes('youtube.com') || url.includes('youtu.be')) {
    platform = 'YouTube';
    icon = YoutubeIcon;
  } else if (url.includes('drive.google.com')) {
    platform = 'Google Drive';
    icon = GlobeIcon; // Could use CloudIcon if available
  } else if (url.includes('github.com')) {
    platform = 'GitHub';
    icon = GithubIcon;
  } else if (url.includes('figma.com')) {
    platform = 'Figma';
    icon = FigmaIcon;
  }

  linkPreview.value = { platform, icon, title: url };
};

const saveLink = () => {
  if (!linkUrl.value) return;
  const name = linkLabel.value.trim() || linkPreview.value?.platform || 'External Link';
  emit('attached', [{
    type: 'link',
    name,
    content: linkUrl.value
  }]);
  resetStore();
};

const resetStore = () => {
  activePick.value = null;
  linkUrl.value = '';
  linkLabel.value = '';
  linkPreview.value = null;
};

const handleOptionClickRef = (id) => {
  if (id === 'link') {
    activePick.value = 'link';
    setTimeout(() => linkInputRef.value?.focus(), 100);
  } else {
    if (id === 'camera' && cameraInput.value) cameraInput.value.click();
    if (id === 'image' && imageInput.value) imageInput.value.click();
    if (id === 'file' && fileInput.value) fileInput.value.click();
  }
};

// Re-map handleOptionClick to the improved version
defineExpose({ handleOptionClick: handleOptionClickRef });

</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active {
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(100%);
}

.zoom-in-enter-active, .zoom-in-leave-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.zoom-in-enter-from, .zoom-in-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(20px);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
