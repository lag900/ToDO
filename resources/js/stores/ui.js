import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUIStore = defineStore('ui', () => {
  // Confirm Dialog State
  const confirmData = ref(null);

  const confirm = (message, options = {}) => {
    return new Promise((resolve) => {
      confirmData.value = {
        message,
        title: options.title || 'Confirm Action',
        confirmText: options.confirmText || 'Confirm',
        cancelText: options.cancelText || 'Cancel',
        variant: options.variant || 'indigo', // indigo, rose, emerald
        resolve
      };
    });
  };

  const closeConfirm = (result) => {
    if (confirmData.value) {
      confirmData.value.resolve(result);
      confirmData.value = null;
    }
  };

  // Toast / Notifications State
  const notifications = ref([]);

  const notify = (message, type = 'info', duration = 4000) => {
    const id = Date.now() + Math.random();
    notifications.value.push({ id, message, type });
    
    setTimeout(() => {
      removeNotification(id);
    }, duration);
  };

  const removeNotification = (id) => {
    notifications.value = notifications.value.filter(n => n.id !== id);
  };

  return {
    confirmData,
    confirm,
    closeConfirm,
    notifications,
    notify,
    removeNotification
  };
});
