<template>
  <div v-if="show" class="fixed inset-0 z-[20000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="$emit('close')"></div>
    
    <div class="relative bg-white dark:bg-slate-900 w-full max-w-md rounded-[2.5rem] shadow-2xl p-8 border border-slate-100 dark:border-slate-800 animate-in zoom-in-95 duration-200">
      <div class="mb-8">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white mb-2">Share {{ type }}</h2>
        <p class="text-slate-500 text-sm">Invite others to collaborate on <span class="font-bold text-indigo-600">{{ entity?.name }}</span></p>
      </div>

      <div class="space-y-6">
        <!-- Invite Form -->
        <div class="space-y-4">
          <div>
            <label for="invite-email" class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Email Address</label>
            <input 
              id="invite-email"
              name="invite_email"
              v-model="form.email" 
              type="email" 
              placeholder="colleague@company.com" 
              class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-4 outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-bold"
            >
          </div>

          <div>
            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Access Level</label>
            <div class="grid grid-cols-3 gap-2">
              <button v-for="role in roles" :key="role.id"
                @click="form.role = role.id"
                :class="[
                  'py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border',
                  form.role === role.id 
                    ? 'bg-indigo-600 text-white border-transparent shadow-lg shadow-indigo-500/30' 
                    : 'bg-slate-50 dark:bg-slate-800 text-slate-500 border-slate-100 dark:border-slate-700 hover:bg-slate-100'
                ]"
              >
                {{ role.label }}
              </button>
            </div>
            <p class="mt-2 text-[10px] text-slate-400 font-medium px-1">{{ activeRoleDesc }}</p>
          </div>

          <button 
            @click="invite" 
            :disabled="loading || !form.email"
            class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-indigo-500/30 hover:bg-indigo-700 disabled:opacity-50 transition-all"
          >
            {{ loading ? 'SENDING...' : 'INVITE PERSON' }}
          </button>
        </div>

        <div class="h-px bg-slate-100 dark:bg-slate-800"></div>

        <!-- Members List -->
        <div class="space-y-4">
          <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-1">Current Members</h3>
          <div class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar pr-2">
            <div v-for="member in members" :key="member.id" class="flex items-center justify-between group">
              <div class="flex items-center gap-3">
                <img :src="member.avatar || 'https://ui-avatars.com/api/?name=' + member.display_name" class="w-8 h-8 rounded-full object-cover">
                <div>
                  <p class="text-sm font-bold text-slate-800 dark:text-white leading-none">{{ member.display_name }}</p>
                  <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ member.pivot?.role || 'member' }}</p>
                </div>
              </div>
              <button 
                v-if="canManage"
                @click="removeMember(member.id)"
                class="opacity-0 group-hover:opacity-100 p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-all"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  show: Boolean,
  type: String, // Workspace or Plan
  id: [Number, String],
  entity: Object
});

const emit = defineEmits(['close']);

const loading = ref(false);
const members = ref([]);
const form = ref({
  email: '',
  role: 'editor'
});

const roles = [
  { id: 'owner', label: 'Owner', desc: 'Full control over data and sharing.' },
  { id: 'editor', label: 'Editor', desc: 'Can add, edit and delete tasks.' },
  { id: 'viewer', label: 'Viewer', desc: 'Read-only access to all data.' }
];

const activeRoleDesc = computed(() => roles.find(r => r.id === form.value.role)?.desc);

const canManage = computed(() => {
    // Current user must be owner to manage
    // This is just a UI check, backend enforces it
    return true; 
});

const fetchMembers = async () => {
  if (!props.id) return;
  try {
    const response = await axios.get(`/api/share/members/${props.type.toLowerCase()}/${props.id}`);
    members.value = response.data;
  } catch (error) {
    console.error('Failed to fetch members');
  }
};

const invite = async () => {
  loading.value = true;
  try {
    await axios.post('/api/share/invite', {
      email: form.value.email,
      role: form.value.role,
      type: props.type,
      id: props.id
    });
    form.value.email = '';
    fetchMembers();
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to invite user');
  } finally {
    loading.value = false;
  }
};

const removeMember = async (userId) => {
  if (!confirm('Remove this member from access?')) return;
  try {
    await axios.delete(`/api/share/members/${props.type.toLowerCase()}/${props.id}/${userId}`);
    fetchMembers();
  } catch (error) {
    alert('Failed to remove member');
  }
};

watch(() => props.show, (newVal) => {
  if (newVal) fetchMembers();
});

onMounted(() => {
  if (props.show) fetchMembers();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
}
</style>
