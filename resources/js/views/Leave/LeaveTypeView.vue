<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Leave Types</h1>
            <p class="mt-1 text-sm text-gray-500">Manage leave types and their configurations</p>
          </div>
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            New Leave Type
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Search and Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Leave Types</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name..."
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              @input="applySearch"
            />
          </div>
        </div>
      </div>

      <!-- Leave Types Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Leave Types</h3>
        </div>
        
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="filteredLeaveTypes.length === 0" class="text-center py-12">
          <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No leave types found</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new leave type.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Leave Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Default Days
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Approval Required
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="leaveType in filteredLeaveTypes" :key="leaveType.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div
                      class="h-4 w-4 rounded-full mr-3"
                      :style="{ backgroundColor: leaveType.color }"
                    ></div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ leaveType.name }}</div>
                      <div v-if="leaveType.description" class="text-sm text-gray-500">
                        {{ leaveType.description }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ leaveType.default_days }} days
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="leaveType.requires_approval ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'"
                  >
                    {{ leaveType.requires_approval ? 'Yes' : 'No' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="editLeaveType(leaveType)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteLeaveType(leaveType)"
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
    <LeaveTypeModal
      v-if="showCreateModal || showEditModal"
      :leave-type="currentLeaveType"
      :is-edit="showEditModal"
      @close="closeModal"
      @created="onLeaveTypeCreated"
      @updated="onLeaveTypeUpdated"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useToast } from 'vue-toastification';
import { PlusIcon, CalendarIcon } from '@heroicons/vue/24/outline';

// Components
import LeaveTypeModal from './partials/LeaveTypeModal.vue';

// Store
const leaveStore = useLeaveStore();
const toast = useToast();

// Reactive data
const showCreateModal = ref(false);
const showEditModal = ref(false);
const currentLeaveType = ref(null);
const searchQuery = ref('');

// Computed
const { leaveTypes, loading } = leaveStore;

const filteredLeaveTypes = computed(() => {
  if (!searchQuery.value) {
    return leaveTypes;
  }
  
  return leaveTypes.filter(lt =>
    lt.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    (lt.description && lt.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
  );
});

// Methods
const applySearch = () => {
  // Search is applied automatically through computed property
};

const editLeaveType = (leaveType) => {
  currentLeaveType.value = leaveType;
  showEditModal.value = true;
};

const deleteLeaveType = async (leaveType) => {
  if (confirm(`Are you sure you want to delete "${leaveType.name}"? This action cannot be undone.`)) {
    const result = await leaveStore.deleteLeaveType(leaveType.id);
    
    if (result.success) {
      toast.success('Leave type deleted successfully');
    } else {
      toast.error(result.error || 'Failed to delete leave type');
    }
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  currentLeaveType.value = null;
};

const onLeaveTypeCreated = () => {
  closeModal();
  toast.success('Leave type created successfully');
};

const onLeaveTypeUpdated = () => {
  closeModal();
  toast.success('Leave type updated successfully');
};

// Lifecycle
onMounted(async () => {
  await leaveStore.fetchLeaveTypes();
});
</script> 