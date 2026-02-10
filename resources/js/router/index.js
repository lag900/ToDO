import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
// Static imports removed to enable lazy loading

const routes = [
    {
        path: '/',
        name: 'landing',
        component: () => import('../pages/Landing.vue'),
        meta: { requiresAuth: false }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('../pages/Dashboard.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/Login.vue'),
        meta: { guestOnly: true }
    },
    {
        path: '/welcome',
        name: 'welcome',
        component: () => import('../pages/Welcome.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/workspace-setup',
        name: 'workspace-setup',
        component: () => import('../pages/WorkspaceSetup.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/settings',
        name: 'settings',
        component: () => import('../pages/Settings.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/accept-invite',
        name: 'accept-invite',
        component: () => import('../pages/AcceptInvite.vue'),
        meta: { requiresAuth: false }
    },
  
    {
        path: '/terms-of-service',
        name: 'terms-of-service',
        component: () => import('../pages/TermsOfService.vue'),
        meta: { requiresAuth: false }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();
    
    // Only initialize/fetch user if the target route requires authentication
    // or if we strictly need to know the auth state for guestOnly routes.
    if ((to.meta.requiresAuth || to.meta.guestOnly) && !auth.initialized) {
        await auth.fetchUser();
    }

    const isAuthenticated = !!auth.user;
    const isWelcomeComplete = auth.user?.has_completed_onboarding;
    const hasWorkspace = (auth.user?.workspaces_count || 0) > 0;

    // 1. Protected routes check
    if (to.meta.requiresAuth && !isAuthenticated) {
        return next({ name: 'login' });
    }

    // 2. Onboarding flow for authenticated users
    if (isAuthenticated && !isWelcomeComplete && to.name !== 'welcome') {
        return next({ name: 'welcome' });
    }

    if (isAuthenticated && isWelcomeComplete && !hasWorkspace && to.name !== 'workspace-setup') {
        return next({ name: 'workspace-setup' });
    }

    // 3. Redirect authenticated users away from Guest pages (login, landing if already in)
    // Note: We only redirect away from 'landing' if we want to force them into the app.
    if (isAuthenticated && isWelcomeComplete && hasWorkspace && (to.name === 'workspace-setup' || to.name === 'welcome' || to.name === 'login')) {
        return next({ name: 'dashboard' });
    }

    if (to.meta.guestOnly && isAuthenticated) {
        return next({ name: 'dashboard' });
    }

    next();
});

export default router;
