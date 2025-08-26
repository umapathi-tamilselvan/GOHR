<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Leave Calendar</h1>
            <p class="mt-1 text-sm text-gray-500">View all leave requests in a calendar format</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="previousMonth"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <ChevronLeftIcon class="h-4 w-4" />
            </button>
            <button
              @click="today"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Today
            </button>
            <button
              @click="nextMonth"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <ChevronRightIcon class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Calendar Header -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-gray-900">
            {{ currentMonthYear }}
          </h2>
          
          <!-- Filters -->
          <div class="flex space-x-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
              <select
                v-model="filters.leave_type_id"
                @change="applyFilters"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option value="">All Types</option>
                <option
                  v-for="leaveType in leaveTypes"
                  :key="leaveType.id"
                  :value="leaveType.id"
                >
                  {{ leaveType.name }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="filters.status"
                @change="applyFilters"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Weekday Headers -->
        <div class="grid grid-cols-7 gap-px bg-gray-200">
          <div
            v-for="day in weekdays"
            :key="day"
            class="bg-gray-50 px-3 py-2 text-center text-sm font-medium text-gray-500 uppercase tracking-wider"
          >
            {{ day }}
          </div>
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7 gap-px bg-gray-200">
          <div
            v-for="day in calendarDays"
            :key="day.date"
            class="min-h-32 bg-white p-2"
            :class="{
              'bg-gray-50': !day.isCurrentMonth,
              'bg-blue-50': day.isToday
            }"
          >
            <!-- Date Number -->
            <div class="flex justify-between items-start mb-1">
              <span
                class="text-sm font-medium"
                :class="{
                  'text-gray-400': !day.isCurrentMonth,
                  'text-blue-600': day.isToday,
                  'text-gray-900': day.isCurrentMonth && !day.isToday
                }"
              >
                {{ day.dayNumber }}
              </span>
            </div>

            <!-- Leave Events -->
            <div class="space-y-1">
              <div
                v-for="leave in getLeavesForDate(day.date)"
                :key="leave.id"
                @click="viewLeave(leave)"
                class="text-xs p-1 rounded cursor-pointer text-white truncate"
                :style="{ backgroundColor: leave.leave_type.color }"
                :title="`${leave.user.name} - ${leave.leave_type.name} (${leave.status})`"
              >
                {{ leave.user.name }} - {{ leave.leave_type.name }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Legend</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div
            v-for="leaveType in leaveTypes"
            :key="leaveType.id"
            class="flex items-center space-x-2"
          >
            <div
              class="w-4 h-4 rounded"
              :style="{ backgroundColor: leaveType.color }"
            ></div>
            <span class="text-sm text-gray-700">{{ leaveType.name }}</span>
          </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
          <h4 class="text-sm font-medium text-gray-900 mb-2">Status Colors</h4>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center space-x-2">
              <div class="w-4 h-4 rounded bg-yellow-500"></div>
              <span class="text-sm text-gray-700">Pending</span>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-4 h-4 rounded bg-green-500"></div>
              <span class="text-sm text-gray-700">Approved</span>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-4 h-4 rounded bg-red-500"></div>
              <span class="text-sm text-gray-700">Rejected</span>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-4 h-4 rounded bg-gray-500"></div>
              <span class="text-sm text-gray-700">Cancelled</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- View Leave Modal -->
    <ViewLeaveModal
      v-if="showViewModal"
      :leave="selectedLeave"
      @close="closeViewModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useToast } from 'vue-toastification';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

// Components
import ViewLeaveModal from './partials/ViewLeaveModal.vue';

// Stores
const leaveStore = useLeaveStore();
const toast = useToast();

// Reactive data
const currentDate = ref(new Date());
const showViewModal = ref(false);
const selectedLeave = ref(null);

const filters = ref({
  leave_type_id: '',
  status: '',
});

// Computed
const { leaveTypes, calendarData, loading } = leaveStore;

const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric'
  });
});

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const startDate = new Date(firstDay);
  startDate.setDate(startDate.getDate() - firstDay.getDay());
  
  const days = [];
  const today = new Date();
  
  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate);
    date.setDate(startDate.getDate() + i);
    
    days.push({
      date: date.toISOString().split('T')[0],
      dayNumber: date.getDate(),
      isCurrentMonth: date.getMonth() === month,
      isToday: date.toDateString() === today.toDateString(),
    });
  }
  
  return days;
});

// Methods
const previousMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1,
    1
  );
  applyFilters();
};

const nextMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1,
    1
  );
  applyFilters();
};

const today = () => {
  currentDate.value = new Date();
  applyFilters();
};

const applyFilters = () => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth() + 1;
  
  leaveStore.fetchCalendarData({
    year,
    month,
    ...filters.value
  });
};

const getLeavesForDate = (date) => {
  if (!calendarData.value || !calendarData.value[date]) {
    return [];
  }
  return calendarData.value[date];
};

const viewLeave = (leave) => {
  selectedLeave.value = leave;
  showViewModal.value = true;
};

const closeViewModal = () => {
  showViewModal.value = false;
  selectedLeave.value = null;
};

// Lifecycle
onMounted(() => {
  applyFilters();
});
</script> 