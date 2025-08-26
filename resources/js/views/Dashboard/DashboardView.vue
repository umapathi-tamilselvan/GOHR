<template>
  <AppLayout>
    <div class="dashboard">
      <!-- Welcome Section -->
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card class="welcome-card" elevation="0">
            <v-card-text class="pa-6">
              <div class="d-flex align-center mb-4">
                <v-avatar size="64" color="primary" class="mr-4">
                  <v-icon size="32" color="white">mdi-account</v-icon>
                </v-avatar>
                <div>
                  <h1 class="text-h4 font-weight-bold primary--text mb-2">
                    Welcome back, {{ userData?.name || 'User' }}!
                  </h1>
                  <p class="text-body-1 text-medium-emphasis">
                    Here's what's happening in your HR system today
                  </p>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Stats Cards -->
      <v-row class="mb-6">
        <v-col cols="12" sm="6" md="3">
          <v-card class="stats-card" elevation="2">
            <v-card-text class="pa-4">
              <div class="d-flex align-center">
                <v-avatar size="48" color="primary" class="mr-3">
                  <v-icon size="24" color="white">mdi-account-group</v-icon>
                </v-avatar>
                <div>
                  <div class="text-h5 font-weight-bold">{{ stats.totalEmployees }}</div>
                  <div class="text-body-2 text-medium-emphasis">Total Employees</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card class="stats-card" elevation="2">
            <v-card-text class="pa-4">
              <div class="d-flex align-center">
                <v-avatar size="48" color="success" class="mr-3">
                  <v-icon size="24" color="white">mdi-clock-check</v-icon>
                </v-avatar>
                <div>
                  <div class="text-h5 font-weight-bold">{{ stats.presentToday }}</div>
                  <div class="text-body-2 text-medium-emphasis">Present Today</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card class="stats-card" elevation="2">
            <v-card-text class="pa-4">
              <div class="d-flex align-center">
                <v-avatar size="48" color="warning" class="mr-3">
                  <v-icon size="24" color="white">mdi-calendar-clock</v-icon>
                </v-avatar>
                <div>
                  <div class="text-h5 font-weight-bold">{{ stats.pendingLeaves }}</div>
                  <div class="text-body-2 text-medium-emphasis">Pending Leaves</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-card class="stats-card" elevation="2">
            <v-card-text class="pa-4">
              <div class="d-flex align-center">
                <v-avatar size="48" color="info" class="mr-3">
                  <v-icon size="24" color="white">mdi-calendar-month</v-icon>
                </v-avatar>
                <div>
                  <div class="text-h5 font-weight-bold">{{ stats.leavesThisMonth }}</div>
                  <div class="text-body-2 text-medium-emphasis">Leaves This Month</div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Quick Actions and Recent Activity -->
      <v-row>
        <v-col cols="12" md="6">
          <v-card class="mb-6" elevation="2">
            <v-card-title class="d-flex align-center">
              <v-icon class="mr-2" color="primary">mdi-lightning-bolt</v-icon>
              Quick Actions
            </v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item
                  v-for="action in quickActions"
                  :key="action.title"
                  :prepend-icon="action.icon"
                  :title="action.title"
                  :subtitle="action.subtitle"
                  @click="action.handler"
                  class="mb-2"
                  rounded="lg"
                  hover
                />
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card class="mb-6" elevation="2">
            <v-card-title class="d-flex align-center">
              <v-icon class="mr-2" color="primary">mdi-clock-outline</v-icon>
              Recent Activity
            </v-card-title>
            <v-card-text>
              <v-timeline density="compact" align="start">
                <v-timeline-item
                  v-for="activity in recentActivities"
                  :key="activity.id"
                  :dot-color="activity.color"
                  size="small"
                >
                  <template v-slot:opposite>
                    <div class="text-caption text-medium-emphasis">
                      {{ activity.time }}
                    </div>
                  </template>
                  <div>
                    <div class="font-weight-medium">{{ activity.title }}</div>
                    <div class="text-caption text-medium-emphasis">
                      {{ activity.description }}
                    </div>
                  </div>
                </v-timeline-item>
              </v-timeline>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Section -->
      <v-row>
        <v-col cols="12" md="8">
          <v-card elevation="2">
            <v-card-title class="d-flex align-center">
              <v-icon class="mr-2" color="primary">mdi-chart-line</v-icon>
              Attendance Overview
            </v-card-title>
            <v-card-text>
              <div class="chart-placeholder pa-8 text-center">
                <v-icon size="64" color="grey-lighten-1">mdi-chart-line</v-icon>
                <div class="text-h6 text-medium-emphasis mt-4">Attendance Chart</div>
                <div class="text-body-2 text-medium-emphasis">
                  Chart will be implemented with Chart.js or similar library
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="2">
            <v-card-title class="d-flex align-center">
              <v-icon class="mr-2" color="primary">mdi-calendar-check</v-icon>
              Leave Calendar
            </v-card-title>
            <v-card-text>
              <div class="calendar-placeholder pa-8 text-center">
                <v-icon size="64" color="grey-lighten-1">mdi-calendar</v-icon>
                <div class="text-h6 text-medium-emphasis mt-4">Leave Calendar</div>
                <div class="text-body-2 text-medium-emphasis">
                  Calendar will be implemented with Vuetify date picker
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppLayout from '@/components/layout/AppLayout.vue';

const router = useRouter();
const authStore = useAuthStore();

// User data
const userData = computed(() => authStore.user);

// Dashboard stats
const stats = ref({
  totalEmployees: 156,
  presentToday: 142,
  pendingLeaves: 8,
  leavesThisMonth: 23
});

// Quick actions
const quickActions = ref([
  {
    title: 'Mark Attendance',
    subtitle: 'Record your daily attendance',
    icon: 'mdi-clock-plus',
    handler: () => router.push('/attendance')
  },
  {
    title: 'Request Leave',
    subtitle: 'Submit a new leave request',
    icon: 'mdi-calendar-plus',
    handler: () => router.push('/leave')
  },
  {
    title: 'View Reports',
    subtitle: 'Access HR reports and analytics',
    icon: 'mdi-chart-bar',
    handler: () => router.push('/reports')
  },
  {
    title: 'Manage Users',
    subtitle: 'Add or edit employee records',
    icon: 'mdi-account-edit',
    handler: () => router.push('/users')
  }
]);

// Recent activities
const recentActivities = ref([
  {
    id: 1,
    title: 'New Leave Request',
    description: 'John Doe requested annual leave for next week',
    time: '2 min ago',
    color: 'primary'
  },
  {
    id: 2,
    title: 'Attendance Marked',
    description: 'Sarah Wilson marked attendance at 9:00 AM',
    time: '15 min ago',
    color: 'success'
  },
  {
    id: 3,
    title: 'Leave Approved',
    description: 'Mike Johnson\'s sick leave was approved',
    time: '1 hour ago',
    color: 'info'
  },
  {
    id: 4,
    title: 'New Employee',
    description: 'Lisa Brown joined the marketing team',
    time: '2 hours ago',
    color: 'warning'
  }
]);

// Lifecycle
onMounted(() => {
  // TODO: Fetch real dashboard data from API
  console.log('Dashboard mounted');
});
</script>

<style scoped>
.dashboard {
  min-height: 100%;
}

.welcome-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.welcome-card .v-card-text {
  color: white;
}

.welcome-card .primary--text {
  color: white !important;
}

.stats-card {
  transition: transform 0.2s ease-in-out;
}

.stats-card:hover {
  transform: translateY(-4px);
}

.chart-placeholder,
.calendar-placeholder {
  background-color: #f8f9fa;
  border-radius: 8px;
  border: 2px dashed #dee2e6;
}

.v-timeline-item::before {
  background-color: #e9ecef;
}
</style> 