/**
 * Task Management Composable
 * Centralized logic for task operations with caching and optimization
 */

import { ref, computed } from 'vue';
import axios from 'axios';

// Request cache to prevent duplicate API calls
const requestCache = new Map();
const CACHE_TTL = 30000; // 30 seconds

// Debounce helper
function debounce(fn, delay) {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
}

// Throttle helper
function throttle(fn, limit) {
  let inThrottle;
  return function (...args) {
    if (!inThrottle) {
      fn.apply(this, args);
      inThrottle = true;
      setTimeout(() => (inThrottle = false), limit);
    }
  };
}

export function useTaskOperations() {
  const loading = ref(false);
  const error = ref(null);

  /**
   * Cached API request
   */
  const cachedRequest = async (key, requestFn, ttl = CACHE_TTL) => {
    const now = Date.now();
    const cached = requestCache.get(key);

    if (cached && now - cached.timestamp < ttl) {
      return cached.data;
    }

    const data = await requestFn();
    requestCache.set(key, { data, timestamp: now });
    return data;
  };

  /**
   * Clear cache for specific key or all
   */
  const clearCache = (key = null) => {
    if (key) {
      requestCache.delete(key);
    } else {
      requestCache.clear();
    }
  };

  /**
   * Optimized task update with debouncing
   */
  const updateTaskDebounced = debounce(async (taskId, updates) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.patch(`/api/tasks/${taskId}`, updates);
      clearCache(`task-${taskId}`);
      clearCache('tasks-list');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Update failed';
      throw err;
    } finally {
      loading.value = false;
    }
  }, 500);

  /**
   * Batch update multiple tasks
   */
  const batchUpdateTasks = async (updates) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/tasks/batch-update', { updates });
      clearCache('tasks-list');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Batch update failed';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Optimized task deletion
   */
  const deleteTask = async (taskId) => {
    loading.value = true;
    error.value = null;
    try {
      await axios.delete(`/api/tasks/${taskId}`);
      clearCache(`task-${taskId}`);
      clearCache('tasks-list');
      return true;
    } catch (err) {
      error.value = err.response?.data?.message || 'Delete failed';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Prefetch task details for faster loading
   */
  const prefetchTask = async (taskId) => {
    try {
      const response = await axios.get(`/api/tasks/${taskId}`);
      requestCache.set(`task-${taskId}`, {
        data: response.data,
        timestamp: Date.now(),
      });
    } catch (err) {
      console.error('Prefetch failed:', err);
    }
  };

  return {
    loading,
    error,
    cachedRequest,
    clearCache,
    updateTaskDebounced,
    batchUpdateTasks,
    deleteTask,
    prefetchTask,
  };
}

/**
 * Optimized list rendering with virtual scrolling support
 */
export function useVirtualList(items, itemHeight = 100) {
  const containerHeight = ref(600);
  const scrollTop = ref(0);

  const visibleRange = computed(() => {
    const start = Math.floor(scrollTop.value / itemHeight);
    const end = Math.ceil((scrollTop.value + containerHeight.value) / itemHeight);
    return { start, end };
  });

  const visibleItems = computed(() => {
    const { start, end } = visibleRange.value;
    return items.value.slice(start, end + 1).map((item, index) => ({
      ...item,
      virtualIndex: start + index,
    }));
  });

  const totalHeight = computed(() => items.value.length * itemHeight);

  const offsetY = computed(() => visibleRange.value.start * itemHeight);

  const handleScroll = throttle((event) => {
    scrollTop.value = event.target.scrollTop;
  }, 16); // ~60fps

  return {
    visibleItems,
    totalHeight,
    offsetY,
    handleScroll,
    containerHeight,
  };
}

/**
 * Optimized form validation
 */
export function useFormValidation() {
  const errors = ref({});

  const validate = (rules, data) => {
    errors.value = {};
    let isValid = true;

    for (const [field, fieldRules] of Object.entries(rules)) {
      const value = data[field];

      if (fieldRules.required && !value) {
        errors.value[field] = `${field} is required`;
        isValid = false;
      }

      if (fieldRules.minLength && value?.length < fieldRules.minLength) {
        errors.value[field] = `${field} must be at least ${fieldRules.minLength} characters`;
        isValid = false;
      }

      if (fieldRules.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        errors.value[field] = 'Invalid email format';
        isValid = false;
      }
    }

    return isValid;
  };

  const clearErrors = () => {
    errors.value = {};
  };

  return {
    errors,
    validate,
    clearErrors,
  };
}
