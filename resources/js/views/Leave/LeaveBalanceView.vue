<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Leave Balances</h1>
            <p class="mt-1 text-sm text-gray-500">Manage and view employee leave balances</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="showInitializeModal = true"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <CalendarIcon class="h-4 w-4 mr-2" />
              Initialize Year
            </button>
            <button
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              New Leave Balance
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
            <select
              v-model="filters.year"
              @change="applyFilters"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
            <select
              v-model="filters.user_id"
              @change="applyFilters"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">All Users</option>
              <option
                v-for="user in users"
                :key="user.id"
                :value="user.id"
              >
                {{ user.name }}
              </option>
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

      <!-- Leave Balances Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Leave Balances</h3>
        </div>
        
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="leaveBalances.length === 0" class="text-center py-12">
          <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No leave balances found</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by initializing leave balances for the year.</p>
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
                  Year
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Total Days
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Used Days
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Remaining
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Usage %
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="balance in leaveBalances" :key="balance.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">
                          {{ balance.user.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ balance.user.name }}</div>
                      <div class="text-sm text-gray-500">{{ balance.user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div
                      class="h-3 w-3 rounded-full mr-2"
                      :style="{ backgroundColor: balance.leave_type.color }"
                    ></div>
                    <span class="text-sm text-gray-900">{{ balance.leave_type.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ balance.year }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ balance.total_days }} days
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ balance.used_days }} days
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ balance.remaining_days }} days
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                      <div
                        class="h-2 rounded-full"
                        :class="getUsageColor(balance.usage_percentage)"
                        :style="{ width: Math.min(balance.usage_percentage, 100) + '%' }"
                      ></div>
                    </div>
                    <span
                      class="text-sm font-medium"
                      :class="getUsageColor(balance.usage_percentage)"
                    >
                      {{ balance.usage_percentage }}%
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="editBalance(balance)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteBalance(balance)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <LeaveBalanceModal
      v-if="showCreateModal || showEditModal"
      :balance="currentBalance"
      :is-edit="showEditModal"
      @close="closeModal"
      @created="onBalanceCreated"
      @updated="onBalanceUpdated"
    />

    <!-- Initialize Year Modal -->
    <InitializeYearModal
      v-if="showInitializeModal"
      @close="showInitializeModal = false"
      @initialized="onYearInitialized"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useUserStore } from '@/stores/user';
import { useToast } from 'vue-toastification';
import { PlusIcon, CalendarIcon } from '@heroicons/vue/24/outline';

// Components
import LeaveBalanceModal from './partials/LeaveBalanceModal.vue';
import InitializeYearModal from './partials/InitializeYearModal.vue';

// Stores
const leaveStore = useLeaveStore();
const userStore = useUserStore();
const toast = useToast();

// Reactive data
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showInitializeModal = ref(false);
const currentBalance = ref(null);

const filters = ref({
  year: new Date().getFullYear(),
  user_id: '',
  leave_type_id: '',
});

// Computed
const { leaveBalances, leaveTypes, loading } = leaveStore;
const { users } = userStore;

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear();
  const years = [];
  for (let i = currentYear - 2; i <= currentYear + 2; i++) {
    years.push(i);
  }
  return years;
});

// Methods
const applyFilters = () => {
  leaveStore.fetchLeaveBalances(filters.value);
};

const clearFilters = () => {
  filters.value = {
    year: new Date().getFullYear(),
    user_id: '',
    leave_type_id: '',
  };
  applyFilters();
};

const editBalance = (balance) => {
  currentBalance.value = balance;
  showEditModal.value = true;
};

const deleteBalance = async (balance) => {
  if (confirm(`Are you sure you want to delete this leave balance for ${balance.user.name}?`)) {
    const result = await leaveStore.deleteLeaveBalance(balance.id);
    
    if (result.success) {
      toast.success('Leave balance deleted successfully');
      applyFilters();
    } else {
      toast.error(result.error || 'Failed to delete leave balance');
    }
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  currentBalance.value = null;
};

const onBalanceCreated = () => {
  closeModal();
  toast.success('Leave balance created successfully');
  applyFilters();
};

const onBalanceUpdated = () => {
  closeModal();
  toast.success('Leave balance updated successfully');
  applyFilters();
};

const onYearInitialized = () => {
  showInitializeModal.value = false;
  toast.success('Leave balances initialized successfully');
  applyFilters();
};

const getUsageColor = (percentage) => {
  if (percentage >= 80) {
    return 'text-red-600 bg-red-500';
  } else if (percentage >= 60) {
    return 'text-yellow-600 bg-yellow-500';
  } else {
    return 'text-green-600 bg-green-500';
  }
};

// Lifecycle
onMounted(async () => {
  await Promise.all([
    leaveStore.fetchLeaveTypes(),
    userStore.fetchUsers(),
    leaveStore.fetchLeaveBalances(filters.value),
  ]);
});
</script> 