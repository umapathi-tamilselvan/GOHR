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
              <ChartBarIcon class="h-6 w-6 text-indigo-600" />
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Leave Report
              </h3>
              <div class="mt-2">
                <!-- Date Range Selection -->
                <div class="mb-6 bg-gray-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-900 mb-3">Select Date Range</h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                      <input
                        v-model="dateRange.start_date"
                        type="date"
                        @change="generateReport"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                      <input
                        v-model="dateRange.end_date"
                        type="date"
                        @change="generateReport"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      />
                    </div>
                    <div class="flex items-end">
                      <button
                        @click="setCurrentMonth"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      >
                        Current Month
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center py-12">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>

                <!-- Report Content -->
                <div v-else-if="reportData" class="space-y-6">
                  <!-- Summary Cards -->
                  <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                      <div class="p-5">
                        <div class="flex items-center">
                          <div class="flex-shrink-0">
                            <CalendarIcon class="h-6 w-6 text-indigo-400" />
                          </div>
                          <div class="ml-5 w-0 flex-1">
                            <dl>
                              <dt class="text-sm font-medium text-gray-500 truncate">Total Leaves</dt>
                              <dd class="text-lg font-medium text-gray-900">{{ reportData.total_leaves }}</dd>
                            </dl>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                      <div class="p-5">
                        <div class="flex items-center">
                          <div class="flex-shrink-0">
                            <CheckCircleIcon class="h-6 w-6 text-green-400" />
                          </div>
                          <div class="ml-5 w-0 flex-1">
                            <dl>
                              <dt class="text-sm font-medium text-gray-500 truncate">Approved</dt>
                              <dd class="text-lg font-medium text-gray-900">{{ reportData.approved_leaves }}</dd>
                            </dl>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                      <div class="p-5">
                        <div class="flex items-center">
                          <div class="flex-shrink-0">
                            <ClockIcon class="h-6 w-6 text-yellow-400" />
                          </div>
                          <div class="ml-5 w-0 flex-1">
                            <dl>
                              <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                              <dd class="text-lg font-medium text-gray-900">{{ reportData.pending_leaves }}</dd>
                            </dl>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                      <div class="p-5">
                        <div class="flex items-center">
                          <div class="flex-shrink-0">
                            <XCircleIcon class="h-6 w-6 text-red-400" />
                          </div>
                          <div class="ml-5 w-0 flex-1">
                            <dl>
                              <dt class="text-sm font-medium text-gray-500 truncate">Rejected</dt>
                              <dd class="text-lg font-medium text-gray-900">{{ reportData.rejected_leaves }}</dd>
                            </dl>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                      <div class="p-5">
                        <div class="flex items-center">
                          <div class="flex-shrink-0">
                            <CalendarDaysIcon class="h-6 w-6 text-blue-400" />
                          </div>
                          <div class="ml-5 w-0 flex-1">
                            <dl>
                              <dt class="text-sm font-medium text-gray-500 truncate">Total Days</dt>
                              <dd class="text-lg font-medium text-gray-900">{{ reportData.total_days }}</dd>
                            </dl>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Charts -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Leave Type Distribution -->
                    <div class="bg-white shadow rounded-lg p-6">
                      <h4 class="text-lg font-medium text-gray-900 mb-4">Leave Type Distribution</h4>
                      <div class="space-y-3">
                        <div
                          v-for="item in reportData.by_leave_type"
                          :key="item.leave_type"
                          class="flex items-center justify-between"
                        >
                          <span class="text-sm text-gray-600">{{ item.leave_type }}</span>
                          <div class="flex items-center space-x-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                              <div
                                class="bg-indigo-600 h-2 rounded-full"
                                :style="{ width: getPercentage(item.count, reportData.total_leaves) + '%' }"
                              ></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 w-8 text-right">
                              {{ item.count }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Status Distribution -->
                    <div class="bg-white shadow rounded-lg p-6">
                      <h4 class="text-lg font-medium text-gray-900 mb-4">Status Distribution</h4>
                      <div class="space-y-3">
                        <div
                          v-for="item in reportData.by_status"
                          :key="item.status"
                          class="flex items-center justify-between"
                        >
                          <span class="text-sm text-gray-600">{{ item.status.charAt(0).toUpperCase() + item.status.slice(1) }}</span>
                          <div class="flex items-center space-x-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                              <div
                                class="h-2 rounded-full"
                                :class="getStatusColor(item.status)"
                                :style="{ width: getPercentage(item.count, reportData.total_leaves) + '%' }"
                              ></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 w-8 text-right">
                              {{ item.count }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Export Options -->
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Export Options</h4>
                    <div class="flex space-x-3">
                      <button
                        @click="exportToCSV"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      >
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export to CSV
                      </button>
                      <button
                        @click="exportToPDF"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      >
                        <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                        Export to PDF
                      </button>
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useToast } from 'vue-toastification';
import {
  ChartBarIcon,
  CalendarIcon,
  CheckCircleIcon,
  ClockIcon,
  XCircleIcon,
  CalendarDaysIcon,
  DocumentArrowDownIcon,
} from '@heroicons/vue/24/outline';

// Props and emits
const emit = defineEmits(['close']);

// Store
const leaveStore = useLeaveStore();
const toast = useToast();

// Reactive data
const loading = ref(false);
const reportData = ref(null);
const dateRange = ref({
  start_date: '',
  end_date: '',
});

// Methods
const setCurrentMonth = () => {
  const now = new Date();
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
  const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  
  dateRange.value = {
    start_date: firstDay.toISOString().split('T')[0],
    end_date: lastDay.toISOString().split('T')[0],
  };
  
  generateReport();
};

const generateReport = async () => {
  if (!dateRange.value.start_date || !dateRange.value.end_date) {
    return;
  }

  loading.value = true;

  try {
    const result = await leaveStore.fetchLeaveReport(
      dateRange.value.start_date,
      dateRange.value.end_date
    );
    
    if (result.success) {
      reportData.value = result.data;
    } else {
      toast.error('Failed to generate report');
    }
  } catch (error) {
    toast.error('An unexpected error occurred');
    console.error('Error generating report:', error);
  } finally {
    loading.value = false;
  }
};

const getPercentage = (value, total) => {
  if (total === 0) return 0;
  return Math.round((value / total) * 100);
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-500',
    approved: 'bg-green-500',
    rejected: 'bg-red-500',
    cancelled: 'bg-gray-500',
  };
  return colors[status] || 'bg-gray-500';
};

const exportToCSV = () => {
  if (!reportData.value) return;
  
  // Implementation for CSV export
  toast.info('CSV export functionality will be implemented soon');
};

const exportToPDF = () => {
  if (!reportData.value) return;
  
  // Implementation for PDF export
  toast.info('PDF export functionality will be implemented soon');
};

// Lifecycle
onMounted(() => {
  setCurrentMonth();
});
</script> 