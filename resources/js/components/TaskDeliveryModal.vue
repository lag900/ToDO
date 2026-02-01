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
          <label class="block text-[12px] font-black uppercase tracking-widest text-slate-400 px-1">Delivery Notes / Summary</label>
          <textarea 
            v-model="notes" 
            placeholder="Summarize what was done, any considerations, and next steps..."
            class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-3xl px-6 py-5 outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-sm min-h-[120px]"
          ></textarea>
        </div>

        <!-- Delivery Items -->
        <div class="space-y-6">
          <div class="flex items-center justify-between px-1">
            <label class="text-[12px] font-black uppercase tracking-widest text-slate-400">Delivery Outputs</label>
            <div class="relative">
                <button @click="showAddItemMenu = !showAddItemMenu" class="h-10 px-5 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:bg-emerald-700 transition-all flex items-center gap-2">
                   <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                   + Add Delivery Item
                </button>
                
                <!-- Simple Dropdown Menu -->
                <div v-if="showAddItemMenu" class="absolute right-0 mt-3 w-48 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 p-2 z-[210] animate-in fade-in slide-in-from-top-2">
                   <button @click="addItem('link'); showAddItemMenu = false" class="w-full flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-left transition-all">
                      <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600">
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                      </div>
                      <span class="text-xs font-bold text-slate-600 dark:text-slate-300">External Link</span>
                   </button>
                   <button @click="$refs.fileInput.click(); showAddItemMenu = false" class="w-full flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-left transition-all">
                      <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                      </div>
                      <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Local File</span>
                   </button>
                </div>
                <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload">
            </div>
          </div>

          <div v-if="items.length === 0" class="border-2 border-dashed border-slate-100 dark:border-slate-800 rounded-3xl p-10 text-center space-y-3">
             <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mx-auto text-slate-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
             </div>
             <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Add your working files or links here</p>
          </div>

          <div v-else class="space-y-3">
            <div v-for="(item, index) in items" :key="index" class="p-4 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl flex items-start gap-4 shadow-sm hover:shadow-md transition-all group">
               <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm', typeIcon(item.type).bg]">
                  <component :is="typeIcon(item.type).icon" class="w-5 h-5 text-white" />
               </div>
               <div class="flex-1 space-y-2">
                 <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white leading-tight truncate max-w-[200px]">{{ item.name }}</h4>
                    <button @click="removeItem(index)" class="p-1 text-slate-300 hover:text-rose-500 transition-colors opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </button>
                 </div>
                 <div class="space-y-3">
                    <input 
                      v-if="item.type === 'link'" 
                      v-model="item.content" 
                      placeholder="Paste your URL here (e.g. Figma, Google Drive)"
                      class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg px-3 py-2 text-[11px] font-medium outline-none focus:border-indigo-500"
                    >
                    <input 
                      v-model="item.description" 
                      placeholder="What is this? (e.g. Final Design Assets, Source Code)"
                      class="w-full bg-slate-100/50 dark:bg-slate-900/50 border-none rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider outline-none focus:ring-1 focus:ring-emerald-500/20"
                    >
                 </div>
               </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-8 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex gap-4">
        <button @click="$emit('close')" class="flex-1 px-6 py-4 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-2xl font-bold hover:bg-slate-200 transition-all border border-slate-200 dark:border-slate-700">Cancel</button>
        <button 
          @click="submitDelivery" 
          :disabled="loading || items.length === 0"
          class="flex-2 px-8 py-4 bg-emerald-600 text-white rounded-2xl font-black shadow-xl shadow-emerald-500/30 hover:bg-emerald-700 disabled:opacity-50 transition-all uppercase tracking-widest text-sm flex items-center justify-center gap-2"
        >
          <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
          {{ loading ? 'Delivering...' : 'Complete Delivery' }}
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
  CheckCircle as SuccessIcon
} from 'lucide-vue-next';

const props = defineProps({
  show: Boolean,
  task: Object
});

const emit = defineEmits(['close', 'delivered']);

const notes = ref('');
const items = ref([]);
const loading = ref(false);
const showAddItemMenu = ref(false);

const typeIcon = (type) => {
  switch (type) {
    case 'link': return { icon: LinkIcon, bg: 'bg-indigo-500' };
    case 'image': return { icon: ImageIcon, bg: 'bg-rose-500' };
    default: return { icon: FileIcon, bg: 'bg-blue-500' };
  }
};

const addItem = (type) => {
  items.value.push({
    type,
    name: type === 'link' ? 'New Link' : 'New File',
    content: '',
    description: ''
  });
};

const removeItem = (index) => {
  items.value.splice(index, 1);
};

const handleFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);

  try {
    loading.value = true;
    const res = await axios.post('/api/delivery-upload', formData);
    
    items.value.push({
      type: file.type.startsWith('image/') ? 'image' : 'file',
      name: res.data.name,
      content: res.data.path,
      description: ''
    });
  } catch (e) {
    alert('Failed to upload file');
  } finally {
    loading.value = false;
    event.target.value = ''; // Reset input
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
