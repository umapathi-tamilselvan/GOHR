<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Leave Management</h1>
            <p class="mt-1 text-sm text-gray-500">Manage leave requests and approvals</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="showCalendar = true"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <CalendarIcon class="h-4 w-4 mr-2" />
              Calendar
            </button>
            <button
              @click="showReport = true"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <ChartBarIcon class="h-4 w-4 mr-2" />
              Report
            </button>
            <button
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              New Leave Request
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Stats -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ClockIcon class="h-6 w-6 text-yellow-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ pendingLeaves.length }}</dd>
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
                  <dd class="text-lg font-medium text-gray-900">{{ approvedLeaves.length }}</dd>
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
                  <dd class="text-lg font-medium text-gray-900">{{ rejectedLeaves.length }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <XMarkIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Cancelled</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ cancelledLeaves.length }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
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

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input
              v-model="filters.start_date"
              type="date"
              @change="applyFilters"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input
              v-model="filters.end_date"
              type="date"
              @change="applyFilters"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <button
            @click="clearFilters"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Leave Requests Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Leave Requests</h3>
        </div>
        
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="leaves.length === 0" class="text-center py-12">
          <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No leave requests</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new leave request.</p>
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
                  Dates
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Days
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="leave in leaves" :key="leave.id">
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
                  {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ leave.total_days }} days
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getStatusClasses(leave.status)"
                  >
                    {{ leave.status.charAt(0).toUpperCase() + leave.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="viewLeave(leave)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      View
                    </button>
                    
                    <button
                      v-if="leave.status === 'pending' && canApproveLeaves"
                      @click="approveLeave(leave.id)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Approve
                    </button>
                    
                    <button
                      v-if="leave.status === 'pending' && canApproveLeaves"
                      @click="openRejectModal(leave)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Reject
                    </button>
                    
                    <button
                      v-if="leave.status === 'pending'"
                      @click="editLeave(leave)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Edit
                    </button>
                    
                    <button
                      v-if="leave.status === 'pending'"
                      @click="cancelLeave(leave.id)"
                      class="text-gray-600 hover:text-gray-900"
                    >
                      Cancel
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
                to
                <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
                of
                <span class="font-medium">{{ pagination.total }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  v-for="page in getPageNumbers()"
                  :key="page"
                  @click="changePage(page)"
                  :class="[
                    page === pagination.current_page
                      ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                >
                  {{ page }}
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Leave Modal -->
    <CreateLeaveModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @created="onLeaveCreated"
    />

    <!-- Edit Leave Modal -->
    <EditLeaveModal
      v-if="showEditModal"
      :leave="currentLeave"
      @close="showEditModal = false"
      @updated="onLeaveUpdated"
    />

    <!-- View Leave Modal -->
    <ViewLeaveModal
      v-if="showViewModal"
      :leave="currentLeave"
      @close="showViewModal = false"
    />

    <!-- Reject Leave Modal -->
    <RejectLeaveModal
      v-if="showRejectModal"
      :leave="currentLeave"
      @close="showRejectModal = false"
      @rejected="onLeaveRejected"
    />

    <!-- Calendar Modal -->
    <LeaveCalendarModal
      v-if="showCalendar"
      @close="showCalendar = false"
    />

    <!-- Report Modal -->
    <LeaveReportModal
      v-if="showReport"
      @close="showReport = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useToast } from 'vue-toastification';
import {
  CalendarIcon,
  ChartBarIcon,
  PlusIcon,
  ClockIcon,
  CheckCircleIcon,
  XCircleIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

// Components
import CreateLeaveModal from './partials/CreateLeaveModal.vue';
import EditLeaveModal from './partials/EditLeaveModal.vue';
import ViewLeaveModal from './partials/ViewLeaveModal.vue';
import RejectLeaveModal from './partials/RejectLeaveModal.vue';
import LeaveCalendarModal from './partials/LeaveCalendarModal.vue';
import LeaveReportModal from './partials/LeaveReportModal.vue';

// Store
const leaveStore = useLeaveStore();
const toast = useToast();

// Reactive data
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showRejectModal = ref(false);
const showCalendar = ref(false);
const showReport = ref(false);
const currentLeave = ref(null);

const filters = ref({
  status: '',
  leave_type_id: '',
  start_date: '',
  end_date: '',
});

// Computed
const {
  leaves,
  leaveTypes,
  loading,
  error,
  pagination,
  pendingLeaves,
  approvedLeaves,
  rejectedLeaves,
  cancelledLeaves,
  canApproveLeaves,
} = leaveStore;

// Methods
const fetchData = async () => {
  await Promise.all([
    leaveStore.fetchLeaves(filters.value),
    leaveStore.fetchLeaveTypes(),
  ]);
};

const applyFilters = () => {
  leaveStore.resetPagination();
  fetchData();
};

const clearFilters = () => {
  filters.value = {
    status: '',
    leave_type_id: '',
    start_date: '',
    end_date: '',
  };
  applyFilters();
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    leaveStore.pagination.current_page = page;
    fetchData();
  }
};

const getPageNumbers = () => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  
  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(last);
    } else if (current >= last - 3) {
      pages.push(1);
      pages.push('...');
      for (let i = last - 4; i <= last; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(last);
    }
  }
  
  return pages;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
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

const viewLeave = (leave) => {
  currentLeave.value = leave;
  showViewModal.value = true;
};

const editLeave = (leave) => {
  currentLeave.value = leave;
  showEditModal.value = true;
};

const openRejectModal = (leave) => {
  currentLeave.value = leave;
  showRejectModal.value = true;
};

const approveLeave = async (leaveId) => {
  const result = await leaveStore.approveLeave(leaveId);
  if (result.success) {
    toast.success('Leave request approved successfully');
    fetchData();
  } else {
    toast.error(result.error || 'Failed to approve leave request');
  }
};

const cancelLeave = async (leaveId) => {
  if (confirm('Are you sure you want to cancel this leave request?')) {
    const result = await leaveStore.deleteLeave(leaveId);
    if (result.success) {
      toast.success('Leave request cancelled successfully');
      fetchData();
    } else {
      toast.error(result.error || 'Failed to cancel leave request');
    }
  }
};

const onLeaveCreated = () => {
  showCreateModal.value = false;
  fetchData();
  toast.success('Leave request created successfully');
};

const onLeaveUpdated = () => {
  showEditModal.value = false;
  fetchData();
  toast.success('Leave request updated successfully');
};

const onLeaveRejected = () => {
  showRejectModal.value = false;
  fetchData();
  toast.success('Leave request rejected successfully');
};

// Lifecycle
onMounted(() => {
  fetchData();
});

// Watch for error changes
watch(() => leaveStore.error, (newError) => {
  if (newError) {
    toast.error(newError);
    leaveStore.clearError();
  }
});
</script> 