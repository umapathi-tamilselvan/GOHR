<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Leave Reports</h1>
            <p class="mt-1 text-sm text-gray-500">Analytics and insights for leave management</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="exportReport"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
              Export
            </button>
            <button
              @click="refreshData"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <ArrowPathIcon class="h-4 w-4 mr-2" />
              Refresh
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <select
              v-model="filters.date_range"
              @change="applyFilters"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="this_month">This Month</option>
              <option value="last_month">Last Month</option>
              <option value="this_quarter">This Quarter</option>
              <option value="this_year">This Year</option>
              <option value="last_year">Last Year</option>
              <option value="custom">Custom Range</option>
            </select>
          </div>

          <div v-if="filters.date_range === 'custom'">
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input
              v-model="filters.start_date"
              type="date"
              @change="applyFilters"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>

          <div v-if="filters.date_range === 'custom'">
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input
              v-model="filters.end_date"
              type="date"
              @change="applyFilters"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
            <select
              v-model="filters.department_id"
              @change="applyFilters"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">All Departments</option>
              <option
                v-for="dept in departments"
                :key="dept.id"
                :value="dept.id"
              >
                {{ dept.name }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CalendarIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Leave Requests</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ summary.total_requests || 0 }}</dd>
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
                  <dd class="text-lg font-medium text-gray-900">{{ summary.approved || 0 }}</dd>
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
                  <dd class="text-lg font-medium text-gray-900">{{ summary.pending || 0 }}</dd>
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
                  <dd class="text-lg font-medium text-gray-900">{{ summary.rejected || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts and Analytics -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Leave Type Distribution -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Leave Type Distribution</h3>
          <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          </div>
          <div v-else-if="leaveTypeData.length === 0" class="text-center py-8">
            <p class="text-gray-500">No data available</p>
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="item in leaveTypeData"
              :key="item.leave_type_id"
              class="flex items-center justify-between"
            >
              <div class="flex items-center space-x-3">
                <div
                  class="w-4 h-4 rounded"
                  :style="{ backgroundColor: item.color }"
                ></div>
                <span class="text-sm font-medium text-gray-900">{{ item.name }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-32 bg-gray-200 rounded-full h-2">
                  <div
                    class="h-2 rounded-full"
                    :style="{ 
                      width: `${item.percentage}%`,
                      backgroundColor: item.color 
                    }"
                  ></div>
                </div>
                <span class="text-sm text-gray-600 w-12 text-right">{{ item.count }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Status Distribution -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Status Distribution</h3>
          <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          </div>
          <div v-else-if="statusData.length === 0" class="text-center py-8">
            <p class="text-gray-500">No data available</p>
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="item in statusData"
              :key="item.status"
              class="flex items-center justify-between"
            >
              <div class="flex items-center space-x-3">
                <div
                  class="w-4 h-4 rounded"
                  :class="getStatusColor(item.status)"
                ></div>
                <span class="text-sm font-medium text-gray-900 capitalize">{{ item.status }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-32 bg-gray-200 rounded-full h-2">
                  <div
                    class="h-2 rounded-full"
                    :class="getStatusColor(item.status)"
                    :style="{ width: `${item.percentage}%` }"
                  ></div>
                </div>
                <span class="text-sm text-gray-600 w-12 text-right">{{ item.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Report Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Detailed Leave Report</h3>
        </div>
        
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="reportData.length === 0" class="text-center py-12">
          <DocumentIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No report data found</h3>
          <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or date range.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Employee
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Leave Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Start Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  End Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Days
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Approved By
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="leave in reportData" :key="leave.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">
                          {{ leave.user.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ leave.user.name }}</div>
                      <div class="text-sm text-gray-500">{{ leave.user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div
                      class="h-3 w-3 rounded-full mr-2"
                      :style="{ backgroundColor: leave.leave_type.color }"
                    ></div>
                    <span class="text-sm text-gray-900">{{ leave.leave_type.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(leave.start_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(leave.end_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ leave.total_days }} days
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getStatusBadgeClass(leave.status)"
                  >
                    {{ leave.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ leave.approver ? leave.approver.name : '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useUserStore } from '@/stores/user';
import { useToast } from 'vue-toastification';
import { 
  CalendarIcon, 
  CheckCircleIcon, 
  ClockIcon, 
  XCircleIcon,
  DocumentIcon,
  DocumentArrowDownIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline';

// Stores
const leaveStore = useLeaveStore();
const userStore = useUserStore();
const toast = useToast();

// Reactive data
const filters = ref({
  date_range: 'this_month',
  start_date: '',
  end_date: '',
  department_id: '',
});

// Computed
const { reportData, summary, leaveTypeData, statusData, loading } = leaveStore;
const { departments } = userStore;

// Methods
const applyFilters = () => {
  // Set date range based on selection
  const today = new Date();
  let startDate, endDate;

  switch (filters.value.date_range) {
    case 'this_month':
      startDate = new Date(today.getFullYear(), today.getMonth(), 1);
      endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
      break;
    case 'last_month':
      startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
      endDate = new Date(today.getFullYear(), today.getMonth(), 0);
      break;
    case 'this_quarter':
      const quarter = Math.floor(today.getMonth() / 3);
      startDate = new Date(today.getFullYear(), quarter * 3, 1);
      endDate = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
      break;
    case 'this_year':
      startDate = new Date(today.getFullYear(), 0, 1);
      endDate = new Date(today.getFullYear(), 11, 31);
      break;
    case 'last_year':
      startDate = new Date(today.getFullYear() - 1, 0, 1);
      endDate = new Date(today.getFullYear() - 1, 11, 31);
      break;
    case 'custom':
      if (filters.value.start_date && filters.value.end_date) {
        startDate = new Date(filters.value.start_date);
        endDate = new Date(filters.value.end_date);
      } else {
        return;
      }
      break;
  }

  if (startDate && endDate) {
    leaveStore.fetchReportData({
      start_date: startDate.toISOString().split('T')[0],
      end_date: endDate.toISOString().split('T')[0],
      department_id: filters.value.department_id,
    });
  }
};

const clearFilters = () => {
  filters.value = {
    date_range: 'this_month',
    start_date: '',
    end_date: '',
    department_id: '',
  };
  applyFilters();
};

const refreshData = () => {
  applyFilters();
  toast.success('Data refreshed successfully');
};

const exportReport = () => {
  // TODO: Implement export functionality
  toast.info('Export functionality coming soon');
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const getStatusColor = (status) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-500';
    case 'approved':
      return 'bg-green-500';
    case 'rejected':
      return 'bg-red-500';
    case 'cancelled':
      return 'bg-gray-500';
    default:
      return 'bg-gray-400';
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'approved':
      return 'bg-green-100 text-green-800';
    case 'rejected':
      return 'bg-red-100 text-red-800';
    case 'cancelled':
      return 'bg-gray-100 text-gray-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// Lifecycle
onMounted(async () => {
  await Promise.all([
    userStore.fetchDepartments(),
  ]);
  applyFilters();
});
</script> 