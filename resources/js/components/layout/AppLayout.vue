<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <AppHeader @toggle-sidebar="toggleSidebar" />
    
    <div class="flex">
      <!-- Sidebar -->
      <AppSidebar
        :is-open="sidebarOpen"
        @close="sidebarOpen = false"
      />
      
      <!-- Main content -->
      <main class="flex-1 lg:ml-64">
        <div class="py-6">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <AppBreadcrumb
              v-if="showBreadcrumbs"
              :items="breadcrumbItems"
            />
            
            <!-- Page content -->
            <div class="mt-6">
              <slot />
            </div>
          </div>
        </div>
      </main>
    </div>
    
    <!-- Mobile sidebar overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 lg:hidden"
      @click="sidebarOpen = false"
    >
      <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppHeader from './AppHeader.vue';
import AppSidebar from './AppSidebar.vue';
import AppBreadcrumb from './AppBreadcrumb.vue';

const route = useRoute();
const authStore = useAuthStore();

// Reactive state
const sidebarOpen = ref(false);

// Computed properties
const showBreadcrumbs = computed(() => {
  return route.path !== '/' && route.name !== 'dashboard';
});

const breadcrumbItems = computed(() => {
  const items = [];
  const pathSegments = route.path.split('/').filter(Boolean);
  
  // Add home
  items.push({
    name: 'Dashboard',
    path: '/',
    current: false
  });
  
  // Build breadcrumb from route segments
  let currentPath = '';
  pathSegments.forEach((segment, index) => {
    currentPath += `/${segment}`;
    const isLast = index === pathSegments.length - 1;
    
    // Convert segment to readable name
    let name = segment.charAt(0).toUpperCase() + segment.slice(1);
    name = name.replace(/-/g, ' ');
    
    items.push({
      name,
      path: currentPath,
      current: isLast
    });
  });
  
  return items;
});

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

// Watch for route changes to close mobile sidebar
watch(() => route.path, () => {
  if (sidebarOpen.value) {
    sidebarOpen.value = false;
  }
});

// Expose methods to parent components
defineExpose({
  toggleSidebar
});
</script> 