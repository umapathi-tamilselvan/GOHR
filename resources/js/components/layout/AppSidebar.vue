<template>
  <div>
    <!-- Desktop sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 shadow-sm transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
      :class="{
        'translate-x-0': isOpen,
        '-translate-x-full': !isOpen
      }"
    >
      <div class="flex flex-col h-full">
        <!-- Sidebar header -->
        <div class="flex items-center justify-center h-16 bg-gradient-to-r from-blue-600 to-blue-700">
          <div class="flex items-center space-x-3">
            <div class="h-8 w-8 bg-white rounded-lg flex items-center justify-center">
              <span class="text-blue-600 font-bold text-lg">G</span>
            </div>
            <span class="text-xl font-bold text-white">GOHR</span>
          </div>
        </div>
        
        <!-- User info section -->
        <div class="px-4 py-3 border-b border-gray-200">
          <div class="flex items-center space-x-3">
            <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="text-blue-600 font-medium text-sm">
                {{ userInitials }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ authStore.userName }}
              </p>
              <p class="text-xs text-gray-500 truncate">
                {{ authStore.userRole }}
              </p>
            </div>
          </div>
        </div>
        
        <!-- Navigation menu -->
        <nav class="flex-1 overflow-y-auto py-4">
          <div class="px-3 space-y-1">
            <!-- Dashboard -->
            <router-link
              to="/"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200"
              :class="[
                $route.path === '/' || $route.path === '/dashboard'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 5v4m8-4v4m-8 6v4m8-4v4"
                />
              </svg>
              Dashboard
            </router-link>
            
            <!-- Users Management -->
            <router-link
              v-if="canAccessUsers"
              to="/users"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200"
              :class="[
                $route.path.startsWith('/users')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                />
              </svg>
              Users
            </router-link>
            
            <!-- Attendance -->
            <router-link
              to="/attendance"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200"
              :class="[
                $route.path.startsWith('/attendance')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              Attendance
            </router-link>
            
            <!-- Attendance List (for managers and above) -->
            <router-link
              v-if="canAccessAttendanceList"
              to="/attendance/list"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ml-6"
              :class="[
                $route.path === '/attendance/list'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                />
              </svg>
              Attendance List
            </router-link>
            
            <!-- Attendance Report (for managers and above) -->
            <router-link
              v-if="canAccessAttendanceReport"
              to="/attendance/report"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ml-6"
              :class="[
                $route.path === '/attendance/report'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
              </svg>
              Reports
            </router-link>
            
            <!-- Leave Management -->
            <router-link
              to="/leaves"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200"
              :class="[
                $route.path.startsWith('/leaves')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
              </svg>
              Leave Management
            </router-link>
            
            <!-- Leave Calendar -->
            <router-link
              to="/leaves/calendar"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ml-6"
              :class="[
                $route.path === '/leaves/calendar'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
              </svg>
              Calendar
            </router-link>
            
            <!-- Leave Report (for managers and above) -->
            <router-link
              v-if="canAccessLeaveReport"
              to="/leaves/report"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ml-6"
              :class="[
                $route.path === '/leaves/report'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
              </svg>
              Leave Reports
            </router-link>
            
            <!-- Leave Types (for HR and above) -->
            <router-link
              v-if="canAccessLeaveTypes"
              to="/leave-types"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ml-6"
              :class="[
                $route.path === '/leave-types'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                />
              </svg>
              Leave Types
            </router-link>
            
            <!-- Leave Balances (for HR and above) -->
            <router-link
              v-if="canAccessLeaveBalances"
              to="/leave-balances"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ml-6"
              :class="[
                $route.path === '/leave-balances'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                />
              </svg>
              Leave Balances
            </router-link>
            
            <!-- Audit Log (for HR and above) -->
            <router-link
              v-if="canAccessAuditLog"
              to="/audit-log"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200"
              :class="[
                $route.path.startsWith('/audit-log')
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              <svg
                class="mr-3 h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              Audit Log
            </router-link>
          </div>
        </nav>
        
        <!-- Sidebar footer -->
        <div class="p-4 border-t border-gray-200">
          <router-link
            to="/profile"
            class="group flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200"
          >
            <svg
              class="mr-3 h-5 w-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
              />
            </svg>
            Profile
          </router-link>
        </div>
      </div>
    </aside>
    
    <!-- Mobile sidebar overlay -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
      @click="$emit('close')"
    ></div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close']);

const authStore = useAuthStore();

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

const canAccessAttendanceList = computed(() => {
  return ['super-admin', 'hr', 'manager'].includes(authStore.userRole);
});

const canAccessAttendanceReport = computed(() => {
  return ['super-admin', 'hr', 'manager'].includes(authStore.userRole);
});

const canAccessAuditLog = computed(() => {
  return ['super-admin', 'hr'].includes(authStore.userRole);
});

const canAccessLeaveReport = computed(() => {
  return ['super-admin', 'hr', 'manager'].includes(authStore.userRole);
});

const canAccessLeaveTypes = computed(() => {
  return ['super-admin', 'hr'].includes(authStore.userRole);
});

const canAccessLeaveBalances = computed(() => {
  return ['super-admin', 'hr'].includes(authStore.userRole);
});
</script> 