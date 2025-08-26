# GOHR Vue.js Migration - Quick Start Guide

## 🚀 Immediate Next Steps (This Week)

### 1. Install Dependencies
```bash
# Navigate to your GOHR project
cd /path/to/GOHR

# Install Vue.js and related packages
npm install vue@^3.4.0 vue-router@^4.2.0 pinia@^2.1.0
npm install @headlessui/vue@^1.7.0 @heroicons/vue@^2.0.0
npm install vue-toastification@^2.0.0

# Install dev dependencies
npm install -D @vitejs/plugin-vue@^5.0.0 @vue/tsconfig@^0.5.0
npm install -D typescript@^5.3.0 vite-plugin-pwa@^0.19.0
npm install -D @tailwindcss/typography@^0.5.10 @tailwindcss/aspect-ratio@^0.4.2
```

### 2. Update Vite Configuration
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg}'],
            },
        }),
    ],
});
```

### 3. Create Vue.js Entry Point
```javascript
// resources/js/app.js
import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(Toast);

app.mount('#app');
```

### 4. Create Main Vue Component
```vue
<!-- resources/js/App.vue -->
<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
// Component logic here
</script>
```

### 5. Set Up Router
```javascript
// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/users',
    name: 'users',
    component: () => import('../views/Users.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/attendance',
    name: 'attendance',
    component: () => import('../views/Attendance.vue'),
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
```

### 6. Update Main Layout
```php
<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GOHR') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <!-- Vue.js will mount here -->
    </div>
</body>
</html>
```

## 📱 First Vue Component (Dashboard)

### Create Dashboard Component
```vue
<!-- resources/js/views/Dashboard.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
          </div>
          <div class="flex items-center">
            <span class="text-sm text-gray-500">Welcome, {{ user?.name }}</span>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <UserGroupIcon class="h-6 w-6 text-gray-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Users
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ stats.totalUsers }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Add more dashboard cards -->
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { UserGroupIcon } from '@heroicons/vue/24/outline';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const user = ref(null);
const stats = ref({
  totalUsers: 0,
  totalAttendance: 0,
  totalLeaves: 0
});

onMounted(async () => {
  user.value = authStore.user;
  // Fetch dashboard stats
  await fetchDashboardStats();
});

const fetchDashboardStats = async () => {
  try {
    // API call to fetch dashboard data
    // stats.value = response.data;
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
  }
};
</script>
```

## 🔧 Development Commands

### Start Development
```bash
# Terminal 1: Start Laravel backend
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev

# Terminal 3: Watch for changes
npm run build -- --watch
```

### Build for Production
```bash
npm run build
```

## 📋 Week 1 Checklist

- [ ] Install all dependencies
- [ ] Configure Vite for Vue.js
- [ ] Set up Vue Router
- [ ] Create basic App.vue component
- [ ] Create first view (Dashboard)
- [ ] Test Vue.js integration
- [ ] Set up Pinia store structure
- [ ] Create basic UI components

## 🎯 Week 2 Goals

- [ ] Complete component library foundation
- [ ] Implement authentication store
- [ ] Create user management components
- [ ] Set up API integration
- [ ] Test component communication

## 🚨 Common Issues & Solutions

### Issue: Vue components not loading
**Solution**: Check Vite configuration and ensure Vue plugin is properly configured

### Issue: Hot reload not working
**Solution**: Ensure both Laravel and Vite servers are running

### Issue: CSS not applying
**Solution**: Check Tailwind CSS configuration and ensure proper imports

## 📚 Learning Resources

- [Vue.js 3 Documentation](https://vuejs.org/)
- [Vue Router 4](https://router.vuejs.org/)
- [Pinia State Management](https://pinia.vuejs.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Headless UI](https://headlessui.com/)

## 🆘 Need Help?

1. **Check console errors** in browser dev tools
2. **Verify all dependencies** are installed correctly
3. **Check file paths** and imports
4. **Review Vite configuration**
5. **Ensure Laravel backend** is running

---

**Ready to start?** Follow these steps and you'll have Vue.js running in GOHR by the end of the week! 🚀 