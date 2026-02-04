<template>
  <div v-if="show" class="fixed inset-0 bg-slate-900/90 backdrop-blur-xl flex items-center justify-center p-4 z-[200]">
    <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col border border-slate-200 dark:border-slate-800 animate-in zoom-in-95 duration-200">
      
      <!-- Header -->
      <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white leading-tight uppercase tracking-tight">Deliver Task</h2>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">{{ task.title }}</p>
          </div>
        </div>
        <button @click="$emit('close')" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto p-8 space-y-8 scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800">
        
        <!-- Delivery Notes -->
        <div class="space-y-3">
          <label for="delivery-notes" class="block text-[12px] font-black uppercase tracking-widest text-slate-400 px-1">Delivery Notes / Summary</label>
          <textarea 
            id="delivery-notes"
            v-model="notes" 
            placeholder="Summarize what was done, any considerations, and next steps..."
            class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-3xl px-6 py-5 outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-sm min-h-[120px]"
          ></textarea>
        </div>

        <!-- Modern Upload Component -->
        <div class="space-y-6">
          <div class="flex items-center justify-between px-1">
            <label class="text-[12px] font-black uppercase tracking-widest text-slate-400">Delivery Outputs</label>
            <button @click="addItem('link')" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-700 flex items-center gap-1.5 transition-colors">
               <LinkIcon class="w-3 h-3" />
               Add External Link
            </button>
          </div>

          <!-- Drop Zone -->
          <div 
             @dragover.prevent="onDragOver"
             @dragleave.prevent="onDragLeave"
             @drop.prevent="onDrop"
             @click="$refs.fileInput.click()"
             :class="[
               'relative border-2 border-dashed rounded-[2.5rem] p-10 transition-all duration-300 cursor-pointer group flex flex-col items-center justify-center gap-4 text-center',
               isDragging ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10 scale-[0.99]' : 'border-slate-200 dark:border-slate-800 hover:border-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-800/50',
               uploadStatus === 'success' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-500/5' : ''
             ]"
          >
             <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" multiple>
             
             <!-- Icon Container -->
             <div class="relative w-20 h-20 flex items-center justify-center">
                <!-- Idle State Icon -->
                <div v-if="uploadStatus === 'idle'" class="w-20 h-20 rounded-3xl bg-slate-100 dark:bg-slate-800 text-slate-400 group-hover:text-emerald-500 group-hover:bg-emerald-50 dark:group-hover:bg-emerald-500/10 transition-all flex items-center justify-center duration-500">
                   <UploadCloudIcon class="w-10 h-10 animate-bounce-subtle" />
                </div>

                <!-- Uploading State (Spinner) -->
                <div v-if="uploadStatus === 'uploading'" class="w-20 h-20 rounded-3xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                   <svg class="animate-spin h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                   </svg>
                </div>

                <!-- Success State (Checkmark) -->
                <div v-if="uploadStatus === 'success'" class="w-20 h-20 rounded-3xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20 animate-in zoom-in duration-300">
                   <SuccessIcon class="w-10 h-10" />
                </div>
             </div>

             <!-- Text Labels -->
             <div class="space-y-1">
                <p v-if="uploadStatus === 'idle'" class="text-sm font-black text-slate-700 dark:text-slate-200 uppercase tracking-tight">
                   {{ isDragging ? 'Drop to upload' : 'Drag & Drop files or click to browse' }}
                </p>
                <p v-if="uploadStatus === 'uploading'" class="text-sm font-black text-emerald-600 uppercase tracking-widest animate-pulse">
                   Uploading...
                </p>
                <p v-if="uploadStatus === 'success'" class="text-sm font-black text-emerald-600 uppercase tracking-widest">
                   Upload Complete!
                </p>
                <p v-if="uploadStatus === 'idle'" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                   Max file size: 30MB
                </p>
             </div>
          </div>

          <!-- Selected Items List -->
          <div v-if="items.length > 0" class="space-y-3 animate-in fade-in slide-in-from-bottom-2 duration-500">
            <div v-for="(item, index) in items" :key="index" class="p-5 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-3xl flex items-start gap-4 shadow-sm hover:shadow-md transition-all group">
               <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-sm transition-transform duration-300 group-hover:scale-110', typeIcon(item.type).bg]">
                  <component :is="typeIcon(item.type).icon" class="w-6 h-6 text-white" />
               </div>
               <div class="flex-1 space-y-2">
                 <div class="flex items-center justify-between">
                    <h4 class="text-base font-black text-slate-800 dark:text-white leading-tight truncate max-w-[250px]">{{ item.name }}</h4>
                    <button @click.stop="removeItem(index)" class="p-2 text-slate-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                        <TrashIcon class="w-4 h-4" />
                    </button>
                 </div>
                 <div class="space-y-3">
                    <div v-if="item.type === 'link'" class="relative">
                      <LinkIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                      <input 
                        v-model="item.content" 
                        placeholder="Paste your URL here (e.g. Figma, Drive)"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl px-9 py-2.5 text-xs font-medium outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                      >
                    </div>
                    <input 
                      v-model="item.description" 
                      placeholder="Add a description for this item..."
                      class="w-full bg-slate-100/30 dark:bg-slate-900/50 border-none rounded-xl px-4 py-2.5 text-[10px] font-bold uppercase tracking-wider outline-none focus:ring-2 focus:ring-emerald-500/10"
                    >
                 </div>
               </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-8 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex gap-4">
        <button @click="$emit('close')" class="flex-1 px-6 py-4 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-[1.5rem] font-bold hover:bg-slate-100 transition-all border border-slate-200 dark:border-slate-700">Cancel</button>
        <button 
          @click="submitDelivery" 
          :disabled="loading || items.length === 0"
          class="flex-[2] px-8 py-4 bg-emerald-600 text-white rounded-[1.5rem] font-black shadow-xl shadow-emerald-500/30 hover:bg-emerald-700 disabled:opacity-50 transition-all uppercase tracking-[0.2em] text-sm flex items-center justify-center gap-3 active:scale-95"
        >
          <SuccessIcon v-if="!loading" class="w-5 h-5" />
          {{ loading ? 'Processing...' : 'Complete Delivery' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { 
  Link as LinkIcon, 
  FileText as FileIcon, 
  Image as ImageIcon,
  CheckCircle as SuccessIcon,
  UploadCloud as UploadCloudIcon,
  Trash2 as TrashIcon
} from 'lucide-vue-next';

const props = defineProps({
  show: Boolean,
  task: Object
});

const emit = defineEmits(['close', 'delivered']);

const notes = ref('');
const items = ref([]);
const loading = ref(false);
const isDragging = ref(false);
const uploadStatus = ref('idle'); // idle, uploading, success, error

const typeIcon = (type) => {
  switch (type) {
    case 'link': return { icon: LinkIcon, bg: 'bg-indigo-500' };
    case 'image': return { icon: ImageIcon, bg: 'bg-rose-500' };
    default: return { icon: FileIcon, bg: 'bg-indigo-600' };
  }
};

const addItem = (type) => {
  items.value.unshift({
    type,
    name: type === 'link' ? 'Project Link' : 'File Asset',
    content: '',
    description: ''
  });
};

const removeItem = (index) => {
  items.value.splice(index, 1);
};

const onDragOver = () => { isDragging.value = true; };
const onDragLeave = () => { isDragging.value = false; };
const onDrop = (e) => {
  isDragging.value = false;
  const files = e.dataTransfer.files;
  if (files.length > 0) {
    handleFiles(files);
  }
};

const handleFileUpload = (e) => {
  const files = e.target.files;
  if (files.length > 0) {
    handleFiles(files);
  }
};

const handleFiles = async (files) => {
  uploadStatus.value = 'uploading';
  let successCount = 0;

  for (const file of files) {
    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await axios.post('/api/delivery-upload', formData);
      items.value.unshift({
        type: file.type.startsWith('image/') ? 'image' : 'file',
        name: file.name,
        content: res.data.path,
        description: ''
      });
      successCount++;
    } catch (e) {
      console.error('Failed to upload file', file.name, e);
      const errorMsg = e.response?.data?.message || e.message || 'Unknown error';
      alert(`Failed to upload ${file.name}: ${errorMsg}`);
    }
  }

  if (successCount > 0) {
    uploadStatus.value = 'success';
    setTimeout(() => {
      uploadStatus.value = 'idle';
    }, 2000);
  } else {
    uploadStatus.value = 'idle';
  }
};

const submitDelivery = async () => {
    if (items.value.length === 0) return;
    
    try {
        loading.value = true;
        await axios.post(`/api/tasks/${props.task.id}/deliver`, {
            notes: notes.value,
            items: items.value
        });
        emit('delivered');
        emit('close');
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to deliver task');
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
@keyframes bounce-subtle {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
.animate-bounce-subtle {
  animation: bounce-subtle 2s infinite ease-in-out;
}
</style>
