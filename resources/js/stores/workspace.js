import { defineStore } from 'pinia';
import axios from 'axios';

export const useWorkspaceStore = defineStore('workspace', {
    state: () => ({
        workspaces: [],
        currentWorkspace: null,
        globalMode: localStorage.getItem('global_mode') === 'true',
        loading: false,
    }),

    actions: {
        async fetchWorkspaces() {
            this.loading = true;
            try {
                const response = await axios.get('/api/workspaces');
                this.workspaces = response.data;
                
                // Initialize current workspace if not set
                if (!this.currentWorkspace && this.workspaces.length > 0) {
                    const lastId = localStorage.getItem('last_workspace_id');
                    const lastWorkspace = this.workspaces.find(w => w.id === parseInt(lastId));
                    this.currentWorkspace = lastWorkspace || this.workspaces[0];
                }
            } catch (error) {
                console.error('Failed to fetch workspaces', error);
            } finally {
                this.loading = false;
            }
        },

        switchWorkspace(workspace) {
            this.currentWorkspace = workspace;
            this.globalMode = false;
            localStorage.setItem('global_mode', 'false');
            localStorage.setItem('last_workspace_id', workspace.id);
        },

        toggleGlobalMode() {
            this.globalMode = !this.globalMode;
            localStorage.setItem('global_mode', this.globalMode);
        },

        async createWorkspace(data) {
            try {
                const response = await axios.post('/api/workspaces', data);
                const newWorkspace = response.data;
                this.workspaces.push(newWorkspace);
                this.switchWorkspace(newWorkspace);
                return newWorkspace;
            } catch (error) {
                console.error('Failed to create workspace', error);
                throw error;
            }
        },

        async deleteWorkspace(id) {
            try {
                await axios.delete(`/api/workspaces/${id}`);
                this.workspaces = this.workspaces.filter(w => w.id !== id);
                if (this.currentWorkspace?.id === id) {
                    this.currentWorkspace = this.workspaces.length > 0 ? this.workspaces[0] : null;
                    if (this.currentWorkspace) {
                        localStorage.setItem('last_workspace_id', this.currentWorkspace.id);
                    } else {
                        localStorage.removeItem('last_workspace_id');
                    }
                }
            } catch (error) {
                console.error('Failed to delete workspace', error);
                throw error;
            }
        }
    }
});
