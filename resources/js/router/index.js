import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: () => import('@/views/Dashboard/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/users',
    name: 'users',
    component: () => import('@/views/Users/UsersView.vue'),
    meta: { requiresAuth: true, roles: ['super-admin', 'hr'] }
  },
  {
    path: '/attendance',
    name: 'attendance',
    component: () => import('@/views/Attendance/AttendanceView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/attendance/list',
    name: 'attendance.list',
    component: () => import('@/views/Attendance/AttendanceListView.vue'),
    meta: { requiresAuth: true, roles: ['super-admin', 'hr', 'manager'] }
  },
  {
    path: '/attendance/report',
    name: 'attendance.report',
    component: () => import('@/views/Attendance/AttendanceReportView.vue'),
    meta: { requiresAuth: true, roles: ['super-admin', 'hr', 'manager'] }
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/Profile/ProfileView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/audit-log',
    name: 'audit-log',
    component: () => import('@/views/AuditLog/AuditLogView.vue'),
    meta: { requiresAuth: true, roles: ['super-admin', 'hr'] }
  },
  {
    path: '/leaves',
    name: 'leaves',
    component: () => import('@/views/Leave/LeaveView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/leaves/calendar',
    name: 'leaves.calendar',
    component: () => import('@/views/Leave/LeaveCalendarView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/leaves/report',
    name: 'leaves.report',
    component: () => import('@/views/Leave/LeaveReportView.vue'),
    meta: { requiresAuth: true, roles: ['super-admin', 'hr', 'manager'] }
  },
  {
    path: '/leave-types',
    name: 'leave-types',
    component: () => import('@/views/Leave/LeaveTypeView.vue'),
    meta: { requiresAuth: true, roles: ['super-admin', 'hr'] }
  },
  {
    path: '/leave-balances',
    name: 'leave-balances',
    component: () => import('@/views/Leave/LeaveBalanceView.vue'),
    meta: { requiresAuth: true, roles: ['super-admin', 'hr'] }
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/Auth/LoginView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/Auth/RegisterView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/views/Auth/ForgotPasswordView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('@/views/Auth/ResetPasswordView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/Errors/NotFoundView.vue'),
    meta: { requiresAuth: false }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Try to check authentication status
      await authStore.checkAuth();
      
      if (!authStore.isAuthenticated) {
        next('/login');
        return;
      }
    }
    
    // Check role-based access
    if (to.meta.roles && !to.meta.roles.includes(authStore.userRole)) {
      next('/dashboard');
      return;
    }
  }
  
  // Redirect authenticated users away from auth pages
  if (authStore.isAuthenticated && ['login', 'register', 'forgot-password', 'reset-password'].includes(to.name)) {
    next('/dashboard');
    return;
  }
  
  next();
});

export default router; 