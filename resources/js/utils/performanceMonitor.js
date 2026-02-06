/**
 * Performance Monitoring Utility
 * Track and optimize frontend performance
 */

class PerformanceMonitor {
  constructor() {
    this.metrics = {
      pageLoad: 0,
      apiCalls: [],
      renders: [],
      interactions: [],
    };
    this.enabled = import.meta.env.DEV; // Only in development
  }

  /**
   * Measure page load time
   */
  measurePageLoad() {
    if (!this.enabled || !window.performance) return;

    window.addEventListener('load', () => {
      const perfData = window.performance.timing;
      const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
      this.metrics.pageLoad = pageLoadTime;

      if (pageLoadTime > 3000) {
        console.warn(`⚠️ Slow page load: ${pageLoadTime}ms`);
      }
    });
  }

  /**
   * Track API call performance
   */
  trackApiCall(url, duration, size) {
    if (!this.enabled) return;

    this.metrics.apiCalls.push({
      url,
      duration,
      size,
      timestamp: Date.now(),
    });

    if (duration > 1000) {
      console.warn(`⚠️ Slow API call: ${url} took ${duration}ms`);
    }

    // Keep only last 50 calls
    if (this.metrics.apiCalls.length > 50) {
      this.metrics.apiCalls.shift();
    }
  }

  /**
   * Track component render time
   */
  trackRender(componentName, duration) {
    if (!this.enabled) return;

    this.metrics.renders.push({
      component: componentName,
      duration,
      timestamp: Date.now(),
    });

    if (duration > 100) {
      console.warn(`⚠️ Slow render: ${componentName} took ${duration}ms`);
    }

    // Keep only last 50 renders
    if (this.metrics.renders.length > 50) {
      this.metrics.renders.shift();
    }
  }

  /**
   * Track user interaction performance
   */
  trackInteraction(action, duration) {
    if (!this.enabled) return;

    this.metrics.interactions.push({
      action,
      duration,
      timestamp: Date.now(),
    });

    if (duration > 200) {
      console.warn(`⚠️ Slow interaction: ${action} took ${duration}ms`);
    }

    // Keep only last 50 interactions
    if (this.metrics.interactions.length > 50) {
      this.metrics.interactions.shift();
    }
  }

  /**
   * Get performance summary
   */
  getSummary() {
    if (!this.enabled) return null;

    const avgApiTime =
      this.metrics.apiCalls.reduce((sum, call) => sum + call.duration, 0) /
        this.metrics.apiCalls.length || 0;

    const avgRenderTime =
      this.metrics.renders.reduce((sum, render) => sum + render.duration, 0) /
        this.metrics.renders.length || 0;

    const avgInteractionTime =
      this.metrics.interactions.reduce((sum, int) => sum + int.duration, 0) /
        this.metrics.interactions.length || 0;

    return {
      pageLoad: this.metrics.pageLoad,
      avgApiTime: Math.round(avgApiTime),
      avgRenderTime: Math.round(avgRenderTime),
      avgInteractionTime: Math.round(avgInteractionTime),
      totalApiCalls: this.metrics.apiCalls.length,
      totalRenders: this.metrics.renders.length,
      totalInteractions: this.metrics.interactions.length,
    };
  }

  /**
   * Log performance summary to console
   */
  logSummary() {
    if (!this.enabled) return;

    const summary = this.getSummary();
    console.group('📊 Performance Summary');
    console.log('Page Load:', summary.pageLoad, 'ms');
    console.log('Avg API Time:', summary.avgApiTime, 'ms');
    console.log('Avg Render Time:', summary.avgRenderTime, 'ms');
    console.log('Avg Interaction Time:', summary.avgInteractionTime, 'ms');
    console.log('Total API Calls:', summary.totalApiCalls);
    console.log('Total Renders:', summary.totalRenders);
    console.log('Total Interactions:', summary.totalInteractions);
    console.groupEnd();
  }

  /**
   * Detect memory leaks
   */
  detectMemoryLeaks() {
    if (!this.enabled || !window.performance || !window.performance.memory) return;

    const checkMemory = () => {
      const memory = window.performance.memory;
      const usedMB = Math.round(memory.usedJSHeapSize / 1048576);
      const limitMB = Math.round(memory.jsHeapSizeLimit / 1048576);
      const percentage = Math.round((usedMB / limitMB) * 100);

      if (percentage > 80) {
        console.warn(`⚠️ High memory usage: ${usedMB}MB / ${limitMB}MB (${percentage}%)`);
      }
    };

    // Check every 30 seconds
    setInterval(checkMemory, 30000);
  }
}

// Create singleton instance
const performanceMonitor = new PerformanceMonitor();

// Auto-start monitoring
if (import.meta.env.DEV) {
  performanceMonitor.measurePageLoad();
  performanceMonitor.detectMemoryLeaks();

  // Log summary every 60 seconds
  setInterval(() => {
    performanceMonitor.logSummary();
  }, 60000);
}

export default performanceMonitor;
