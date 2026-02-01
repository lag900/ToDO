import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loading: false,
        initialized: false,
    }),

    actions: {
        async fetchUser() {
            this.loading = true;
            try {
                const response = await axios.get('/api/me');
                this.user = response.data;
            } catch (error) {
                this.user = null;
            } finally {
                this.loading = false;
                this.initialized = true;
            }
        },

        async logout() {
            try {
                await axios.post('/api/logout');
                this.user = null;
                // Use replace to prevent "back" button issues with session
                window.location.replace('/login');
            } catch (error) {
                console.error('Logout failed', error);
            }
        },

        async completeOnboarding(displayName) {
            try {
                const response = await axios.post('/api/complete-onboarding', {
                    display_name: displayName
                });
                this.user = response.data;
                return true;
            } catch (error) {
                console.error('Onboarding failed', error);
                throw error;
            }
        }
    }
});
