<template>
  <AppLayout>
    <template #default>
      <!-- Page header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-sm text-gray-600">
          Welcome back, {{ authStore.userName }}! Here's what's happening today.
        </p>
      </div>
      
      <!-- Role-based dashboard content -->
      <div v-if="authStore.userRole === 'super-admin'">
        <SuperAdminDashboard />
      </div>
      <div v-else-if="authStore.userRole === 'hr'">
        <HRDashboard />
      </div>
      <div v-else-if="authStore.userRole === 'manager'">
        <ManagerDashboard />
      </div>
      <div v-else>
        <EmployeeDashboard />
      </div>
    </template>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import AppLayout from '@/components/layout/AppLayout.vue';
import SuperAdminDashboard from './SuperAdminDashboard.vue';
import HRDashboard from './HRDashboard.vue';
import ManagerDashboard from './ManagerDashboard.vue';
import EmployeeDashboard from './EmployeeDashboard.vue';

const authStore = useAuthStore();

onMounted(() => {
  // Initialize dashboard data if needed
  console.log('Dashboard mounted for role:', authStore.userRole);
});
</script> 