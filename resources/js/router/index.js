import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../pages/Dashboard.vue';
import Login from '../pages/Login.vue';
import Welcome from '../pages/Welcome.vue';
import { useAuthStore } from '../stores/auth';

import WorkspaceSetup from '../pages/WorkspaceSetup.vue';
import Settings from '../pages/Settings.vue';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        alias: '/dashboard',
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guestOnly: true }
    },
    {
        path: '/welcome',
        name: 'welcome',
        component: Welcome,
        meta: { requiresAuth: true }
    },
    {
        path: '/workspace-setup',
        name: 'workspace-setup',
        component: WorkspaceSetup,
        meta: { requiresAuth: true }
    },
    {
        path: '/settings',
        name: 'settings',
        component: Settings,
        meta: { requiresAuth: true }
    },
    {
        path: '/accept-invite',
        name: 'accept-invite',
        component: () => import('../pages/AcceptInvite.vue'),
        meta: { requiresAuth: false }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();
    
    if (!auth.initialized) {
        await auth.fetchUser();
    }

    const isAuthenticated = !!auth.user;
    const isWelcomeComplete = auth.user?.has_completed_onboarding;
    const hasWorkspace = (auth.user?.workspaces_count || 0) > 0;

    if (to.meta.requiresAuth && !isAuthenticated) {
        return next({ name: 'login' });
    }

    if (isAuthenticated && !isWelcomeComplete && to.name !== 'welcome') {
        return next({ name: 'welcome' });
    }

    if (isAuthenticated && isWelcomeComplete && !hasWorkspace && to.name !== 'workspace-setup') {
        return next({ name: 'workspace-setup' });
    }

    if (isAuthenticated && isWelcomeComplete && hasWorkspace && (to.name === 'workspace-setup' || to.name === 'welcome' || to.name === 'login')) {
        return next({ name: 'dashboard' });
    }

    if (to.meta.guestOnly && isAuthenticated) {
        return next({ name: 'dashboard' });
    }

    next();
});

export default router;
