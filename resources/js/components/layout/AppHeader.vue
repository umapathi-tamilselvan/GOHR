<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Left side - Logo and Navigation Toggle -->
        <div class="flex items-center">
          <!-- Mobile menu button -->
          <button
            @click="$emit('toggleSidebar')"
            class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
          >
            <span class="sr-only">Open main menu</span>
            <svg
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          </button>
          
          <!-- Logo -->
          <div class="flex-shrink-0 flex items-center">
            <router-link to="/" class="flex items-center">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">G</span>
              </div>
              <span class="ml-2 text-xl font-bold text-gray-900">GOHR</span>
            </router-link>
          </div>
        </div>
        
        <!-- Center - Search (hidden on mobile) -->
        <div class="hidden md:flex flex-1 max-w-lg mx-8">
          <div class="w-full">
            <label for="search" class="sr-only">Search</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
              </div>
              <input
                id="search"
                v-model="searchQuery"
                @keyup.enter="handleSearch"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="Search..."
                type="search"
              />
            </div>
          </div>
        </div>
        
        <!-- Right side - User menu and notifications -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <button
            @click="toggleNotifications"
            class="p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
          >
            <span class="sr-only">View notifications</span>
            <svg
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 00-6 6v3.75l-.75.75V19.5h13.5v-5.25l-.75-.75V9.75a6 6 0 00-6-6z"
              />
            </svg>
            <!-- Notification badge -->
            <span
              v-if="notificationCount > 0"
              class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-400"
            ></span>
          </button>
          
          <!-- User menu -->
          <div class="relative">
            <button
              @click="toggleUserMenu"
              class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="sr-only">Open user menu</span>
              <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center">
                <span class="text-white font-medium text-sm">
                  {{ userInitials }}
                </span>
              </div>
              <span class="ml-2 text-gray-700 hidden md:block">
                {{ authStore.userName }}
              </span>
              <svg
                class="ml-1 h-4 w-4 text-gray-400 hidden md:block"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>
            
            <!-- User dropdown menu -->
            <div
              v-if="userMenuOpen"
              class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
            >
              <router-link
                to="/profile"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click="userMenuOpen = false"
              >
                Your Profile
              </router-link>
              <router-link
                to="/settings"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click="userMenuOpen = false"
              >
                Settings
              </router-link>
              <div class="border-t border-gray-100"></div>
              <button
                @click="handleLogout"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                Sign out
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Mobile menu -->
    <div
      v-if="mobileMenuOpen"
      class="lg:hidden border-t border-gray-200 bg-white"
    >
      <div class="px-2 pt-2 pb-3 space-y-1">
        <router-link
          to="/"
          class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50"
          @click="mobileMenuOpen = false"
        >
          Dashboard
        </router-link>
        <router-link
          v-if="canAccessUsers"
          to="/users"
          class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50"
          @click="mobileMenuOpen = false"
        >
          Users
        </router-link>
        <router-link
          to="/attendance"
          class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50"
          @click="mobileMenuOpen = false"
        >
          Attendance
        </router-link>
        <router-link
          v-if="canAccessAuditLog"
          to="/audit-log"
          class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50"
          @click="mobileMenuOpen = false"
        >
          Audit Log
        </router-link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

// Reactive state
const mobileMenuOpen = ref(false);
const userMenuOpen = ref(false);
const searchQuery = ref('');
const notificationCount = ref(0);

// Computed properties
const userInitials = computed(() => {
  if (!authStore.userName) return 'U';
  return authStore.userName
    .split(' ')
    .map(name => name.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

const canAccessUsers = computed(() => {
  return ['super-admin', 'hr'].includes(authStore.userRole);
});

const canAccessAuditLog = computed(() => {
  return ['super-admin', 'hr'].includes(authStore.userRole);
});

// Methods
// Remove this method as it's now handled by parent

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value;
  if (userMenuOpen.value) {
    mobileMenuOpen.value = false;
  }
};

const toggleNotifications = () => {
  // TODO: Implement notifications panel
  console.log('Toggle notifications');
};

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    // TODO: Implement search functionality
    console.log('Search for:', searchQuery.value);
    searchQuery.value = '';
  }
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

// Close menus when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    userMenuOpen.value = false;
    mobileMenuOpen.value = false;
  }
};

// Define emits
defineEmits(['toggleSidebar']);

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script> 