<template>
  <AppLayout>
    <template #default>
      <!-- Page header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Attendance Management</h1>
            <p class="mt-2 text-sm text-gray-600">
              Track your daily attendance and manage time records.
            </p>
          </div>
          <div class="flex space-x-3">
            <BaseButton
              @click="showManualEntryModal = true"
              variant="secondary"
              size="lg"
              v-if="canCreateManualEntry"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Manual Entry
            </BaseButton>
            <BaseButton
              @click="refreshAttendance"
              variant="primary"
              size="lg"
              :loading="loading"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </BaseButton>
          </div>
        </div>
      </div>
      
      <!-- Current day status -->
      <div class="mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">
                Today's Attendance - {{ formatDate(today) }}
              </h3>
              <p class="text-sm text-gray-600">
                {{ getCurrentTime() }}
              </p>
            </div>
            <div class="flex space-x-3">
              <BaseButton
                v-if="!todayAttendance?.check_in"
                @click="checkIn"
                variant="success"
                size="lg"
                :loading="checkInLoading"
                class="min-w-[120px]"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Check In
              </BaseButton>
              <BaseButton
                v-if="todayAttendance?.check_in && !todayAttendance?.check_out"
                @click="checkOut"
                variant="warning"
                size="lg"
                :loading="checkOutLoading"
                class="min-w-[120px]"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Check Out
              </BaseButton>
              <div v-if="todayAttendance?.check_in && todayAttendance?.check_out" class="text-center">
                <div class="text-sm text-gray-500">Completed</div>
                <div class="text-lg font-semibold text-green-600">
                  {{ calculateWorkHours(todayAttendance.check_in, todayAttendance.check_out) }}
                </div>
              </div>
            </div>
          </div>
          
          <!-- Today's summary -->
          <div v-if="todayAttendance" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-blue-900">Check In</p>
                  <p class="text-lg font-semibold text-blue-700">
                    {{ todayAttendance.check_in ? formatTime(todayAttendance.check_in) : 'Not checked in' }}
                  </p>
                </div>
              </div>
            </div>
            
            <div class="bg-orange-50 rounded-lg p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-orange-900">Check Out</p>
                  <p class="text-lg font-semibold text-orange-700">
                    {{ todayAttendance.check_out ? formatTime(todayAttendance.check_out) : 'Not checked out' }}
                  </p>
                </div>
              </div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-green-900">Work Hours</p>
                  <p class="text-lg font-semibold text-green-700">
                    {{ calculateWorkHours(todayAttendance.check_in, todayAttendance.check_out) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Weekly summary -->
      <div class="mb-6">
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">This Week's Summary</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
              <div
                v-for="day in weeklySummary"
                :key="day.date"
                class="text-center p-3 rounded-lg"
                :class="day.isToday ? 'bg-blue-100 border-2 border-blue-300' : 'bg-gray-50'"
              >
                <div class="text-xs font-medium text-gray-500 uppercase">
                  {{ day.dayName }}
                </div>
                <div class="text-sm font-semibold text-gray-900">
                  {{ day.date }}
                </div>
                <div class="text-xs text-gray-600 mt-1">
                  {{ day.hours || 'N/A' }}
                </div>
                <div class="mt-1">
                  <span
                    v-if="day.status === 'present'"
                    class="inline-block w-2 h-2 bg-green-500 rounded-full"
                  ></span>
                  <span
                    v-else-if="day.status === 'absent'"
                    class="inline-block w-2 h-2 bg-red-500 rounded-full"
                  ></span>
                  <span
                    v-else
                    class="inline-block w-2 h-2 bg-gray-400 rounded-full"
                  ></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recent attendance records -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Recent Attendance Records</h3>
            <router-link
              to="/attendance/list"
              class="text-sm font-medium text-blue-600 hover:text-blue-500"
            >
              View All →
            </router-link>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Check In
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Check Out
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Hours
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="record in recentRecords" :key="record.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(record.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.check_in ? formatTime(record.check_in) : 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.check_out ? formatTime(record.check_out) : 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ calculateWorkHours(record.check_in, record.check_out) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getStatusBadgeClasses(record)">
                    {{ getStatusText(record) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty state -->
        <div v-if="!recentRecords.length" class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No attendance records</h3>
          <p class="mt-1 text-sm text-gray-500">
            Your attendance records will appear here once you start checking in and out.
          </p>
        </div>
      </div>
      
      <!-- Manual Entry Modal -->
      <ManualEntryModal
        v-if="showManualEntryModal"
        @close="showManualEntryModal = false"
        @saved="handleManualEntrySaved"
      />
    </template>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import ManualEntryModal from './partials/ManualEntryModal.vue';

const authStore = useAuthStore();

// Reactive state
const loading = ref(false);
const checkInLoading = ref(false);
const checkOutLoading = ref(false);
const showManualEntryModal = ref(false);
const todayAttendance = ref(null);
const recentRecords = ref([]);
const currentTime = ref(new Date());

// Computed properties
const today = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const weeklySummary = computed(() => {
  const days = [];
  const today = new Date();
  
  for (let i = 6; i >= 0; i--) {
    const date = new Date(today);
    date.setDate(today.getDate() - i);
    
    const dateStr = date.toISOString().split('T')[0];
    const record = recentRecords.value.find(r => r.date === dateStr);
    
    days.push({
      date: date.getDate(),
      dayName: date.toLocaleDateString('en-US', { weekday: 'short' }),
      isToday: dateStr === today.value,
      status: record ? 'present' : 'absent',
      hours: record ? calculateWorkHours(record.check_in, record.check_out) : null
    });
  }
  
  return days;
});

const canCreateManualEntry = computed(() => {
  return ['super-admin', 'hr', 'manager'].includes(authStore.userRole);
});

// Methods
const getCurrentTime = () => {
  return currentTime.value.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  });
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatTime = (timeString) => {
  if (!timeString) return 'N/A';
  return new Date(timeString).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  });
};

const calculateWorkHours = (checkIn, checkOut) => {
  if (!checkIn || !checkOut) return 'N/A';
  
  const start = new Date(checkIn);
  const end = new Date(checkOut);
  const diffMs = end - start;
  const diffHours = diffMs / (1000 * 60 * 60);
  
  const hours = Math.floor(diffHours);
  const minutes = Math.floor((diffHours - hours) * 60);
  
  return `${hours}h ${minutes}m`;
};

const getStatusText = (record) => {
  if (!record.check_in) return 'Absent';
  if (!record.check_out) return 'Present';
  return 'Completed';
};

const getStatusBadgeClasses = (record) => {
  if (!record.check_in) return 'bg-red-100 text-red-800';
  if (!record.check_out) return 'bg-yellow-100 text-yellow-800';
  return 'bg-green-100 text-green-800';
};

const checkIn = async () => {
  checkInLoading.value = true;
  try {
    // TODO: Implement actual API call
    const now = new Date();
    todayAttendance.value = {
      ...todayAttendance.value,
      check_in: now.toISOString()
    };
    
    // Add to recent records
    const todayRecord = {
      id: Date.now(),
      date: today.value,
      check_in: now.toISOString(),
      check_out: null
    };
    
    recentRecords.value.unshift(todayRecord);
    
    // Show success message
    console.log('Checked in successfully');
  } catch (error) {
    console.error('Check-in failed:', error);
  } finally {
    checkInLoading.value = false;
  }
};

const checkOut = async () => {
  checkOutLoading.value = true;
  try {
    // TODO: Implement actual API call
    const now = new Date();
    todayAttendance.value = {
      ...todayAttendance.value,
      check_out: now.toISOString()
    };
    
    // Update recent record
    const todayRecord = recentRecords.value.find(r => r.date === today.value);
    if (todayRecord) {
      todayRecord.check_out = now.toISOString();
    }
    
    // Show success message
    console.log('Checked out successfully');
  } catch (error) {
    console.error('Check-out failed:', error);
  } finally {
    checkOutLoading.value = false;
  }
};

const refreshAttendance = async () => {
  loading.value = true;
  try {
    // TODO: Implement actual API call to refresh data
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    // Mock data for demonstration
    todayAttendance.value = {
      id: 1,
      date: today.value,
      check_in: null,
      check_out: null
    };
    
    recentRecords.value = [
      {
        id: 1,
        date: '2025-01-25',
        check_in: '2025-01-25T09:00:00Z',
        check_out: '2025-01-25T17:00:00Z'
      },
      {
        id: 2,
        date: '2025-01-24',
        check_in: '2025-01-24T08:30:00Z',
        check_out: '2025-01-24T17:30:00Z'
      }
    ];
  } catch (error) {
    console.error('Failed to refresh attendance:', error);
  } finally {
    loading.value = false;
  }
};

const handleManualEntrySaved = () => {
  showManualEntryModal.value = false;
  refreshAttendance();
};

// Timer for current time
let timer;

// Lifecycle
onMounted(() => {
  refreshAttendance();
  
  // Update current time every second
  timer = setInterval(() => {
    currentTime.value = new Date();
  }, 1000);
});

onUnmounted(() => {
  if (timer) {
    clearInterval(timer);
  }
});
</script> 