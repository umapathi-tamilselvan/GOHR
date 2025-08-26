<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
              <CalendarIcon class="h-6 w-6 text-indigo-600" />
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Leave Calendar
              </h3>
              <div class="mt-2">
                <!-- Calendar Navigation -->
                <div class="flex items-center justify-between mb-6">
                  <button
                    @click="previousMonth"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    <ChevronLeftIcon class="h-4 w-4 mr-1" />
                    Previous
                  </button>
                  
                  <h4 class="text-lg font-medium text-gray-900">
                    {{ getMonthName(currentMonth) }} {{ currentYear }}
                  </h4>
                  
                  <button
                    @click="nextMonth"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    Next
                    <ChevronRightIcon class="h-4 w-4 ml-1" />
                  </button>
                </div>

                <!-- Calendar Grid -->
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                  <!-- Calendar Header -->
                  <div class="grid grid-cols-7 gap-px bg-gray-200">
                    <div
                      v-for="day in weekDays"
                      :key="day"
                      class="bg-gray-50 px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      {{ day }}
                    </div>
                  </div>

                  <!-- Calendar Body -->
                  <div class="grid grid-cols-7 gap-px bg-gray-200">
                    <div
                      v-for="date in calendarDates"
                      :key="date.key"
                      class="min-h-[80px] bg-white px-2 py-1 relative"
                      :class="{
                        'bg-gray-50': !date.isCurrentMonth,
                        'bg-blue-50': date.isToday
                      }"
                    >
                      <!-- Date Number -->
                      <div class="text-sm font-medium text-gray-900 mb-1">
                        {{ date.day }}
                      </div>

                      <!-- Leave Events -->
                      <div v-if="date.leaves && date.leaves.length > 0" class="space-y-1">
                        <div
                          v-for="leave in date.leaves.slice(0, 2)"
                          :key="leave.id"
                          class="text-xs p-1 rounded truncate cursor-pointer hover:opacity-80"
                          :style="{ backgroundColor: leave.color + '20', color: leave.color }"
                          :title="`${leave.user_name} - ${leave.leave_type} (${leave.status})`"
                          @click="viewLeaveDetails(leave)"
                        >
                          {{ leave.user_name }}
                        </div>
                        <div
                          v-if="date.leaves.length > 2"
                          class="text-xs text-gray-500 text-center"
                        >
                          +{{ date.leaves.length - 2 }} more
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Legend -->
                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                  <h5 class="text-sm font-medium text-gray-900 mb-3">Legend</h5>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div
                      v-for="leaveType in leaveTypes"
                      :key="leaveType.id"
                      class="flex items-center"
                    >
                      <div
                        class="w-3 h-3 rounded-full mr-2"
                        :style="{ backgroundColor: leaveType.color }"
                      ></div>
                      <span class="text-sm text-gray-600">{{ leaveType.name }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            @click="$emit('close')"
            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Leave Details Modal -->
    <div
      v-if="selectedLeave"
      class="fixed inset-0 z-60 overflow-y-auto"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="selectedLeave = null"></div>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                <CalendarIcon class="h-6 w-6 text-indigo-600" />
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                  Leave Details
                </h3>
                <div class="mt-2 space-y-3">
                  <div>
                    <span class="text-gray-500">Employee:</span>
                    <div class="font-medium text-gray-900">{{ selectedLeave.user_name }}</div>
                  </div>
                  <div>
                    <span class="text-gray-500">Leave Type:</span>
                    <div class="font-medium text-gray-900">{{ selectedLeave.leave_type }}</div>
                  </div>
                  <div>
                    <span class="text-gray-500">Status:</span>
                    <span
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2"
                      :class="getStatusClasses(selectedLeave.status)"
                    >
                      {{ selectedLeave.status.charAt(0).toUpperCase() + selectedLeave.status.slice(1) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              @click="selectedLeave = null"
              class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useToast } from 'vue-toastification';
import {
  CalendarIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline';

// Props and emits
const emit = defineEmits(['close']);

// Store
const leaveStore = useLeaveStore();
const toast = useToast();

// Reactive data
const currentMonth = ref(new Date().getMonth() + 1);
const currentYear = ref(new Date().getFullYear());
const calendarData = ref({});
const selectedLeave = ref(null);

// Computed
const { leaveTypes } = leaveStore;

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const calendarDates = computed(() => {
  const dates = [];
  const firstDay = new Date(currentYear.value, currentMonth.value - 1, 1);
  const lastDay = new Date(currentYear.value, currentMonth.value, 0);
  const startDate = new Date(firstDay);
  startDate.setDate(startDate.getDate() - firstDay.getDay());
  
  const endDate = new Date(lastDay);
  endDate.setDate(endDate.getDate() + (6 - lastDay.getDay()));
  
  const today = new Date();
  
  for (let date = new Date(startDate); date <= endDate; date.setDate(date.getDate() + 1)) {
    const dateKey = date.toISOString().split('T')[0];
    const isCurrentMonth = date.getMonth() === currentMonth.value - 1;
    const isToday = date.toDateString() === today.toDateString();
    
    dates.push({
      key: dateKey,
      day: date.getDate(),
      date: dateKey,
      isCurrentMonth,
      isToday,
      leaves: calendarData.value[dateKey] || [],
    });
  }
  
  return dates;
});

// Methods
const getMonthName = (month) => {
  const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  return months[month - 1];
};

const previousMonth = () => {
  if (currentMonth.value === 1) {
    currentMonth.value = 12;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
  fetchCalendarData();
};

const nextMonth = () => {
  if (currentMonth.value === 12) {
    currentMonth.value = 1;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
  fetchCalendarData();
};

const fetchCalendarData = async () => {
  try {
    const result = await leaveStore.fetchLeaveCalendar(currentMonth.value, currentYear.value);
    if (result.success) {
      calendarData.value = result.data;
    }
  } catch (error) {
    console.error('Error fetching calendar data:', error);
  }
};

const viewLeaveDetails = (leave) => {
  selectedLeave.value = leave;
};

const getStatusClasses = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
  };
  return classes[status] || classes.pending;
};

// Lifecycle
onMounted(async () => {
  await Promise.all([
    leaveStore.fetchLeaveTypes(),
    fetchCalendarData(),
  ]);
});
</script> 