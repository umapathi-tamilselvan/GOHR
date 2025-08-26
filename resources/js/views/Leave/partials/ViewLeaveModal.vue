<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
              <EyeIcon class="h-6 w-6 text-indigo-600" />
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Leave Request Details
              </h3>
              <div class="mt-2 space-y-4">
                <!-- Employee Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-900 mb-3">Employee Information</h4>
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
                </div>

                <!-- Leave Details -->
                <div class="bg-gray-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-900 mb-3">Leave Details</h4>
                  <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <span class="text-gray-500">Leave Type:</span>
                      <div class="flex items-center mt-1">
                        <div
                          class="h-3 w-3 rounded-full mr-2"
                          :style="{ backgroundColor: leave.leave_type.color }"
                        ></div>
                        <span class="font-medium text-gray-900">{{ leave.leave_type.name }}</span>
                      </div>
                    </div>
                    <div>
                      <span class="text-gray-500">Total Days:</span>
                      <div class="font-medium text-gray-900 mt-1">{{ leave.total_days }} days</div>
                    </div>
                    <div>
                      <span class="text-gray-500">Start Date:</span>
                      <div class="font-medium text-gray-900 mt-1">{{ formatDate(leave.start_date) }}</div>
                    </div>
                    <div>
                      <span class="text-gray-500">End Date:</span>
                      <div class="font-medium text-gray-900 mt-1">{{ formatDate(leave.end_date) }}</div>
                    </div>
                  </div>
                </div>

                <!-- Status Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-900 mb-3">Status Information</h4>
                  <div class="space-y-3">
                    <div class="flex justify-between">
                      <span class="text-gray-500">Status:</span>
                      <span
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getStatusClasses(leave.status)"
                      >
                        {{ leave.status.charAt(0).toUpperCase() + leave.status.slice(1) }}
                      </span>
                    </div>
                    
                    <div v-if="leave.approved_by" class="flex justify-between">
                      <span class="text-gray-500">Approved By:</span>
                      <span class="font-medium text-gray-900">{{ leave.approver?.name || 'Unknown' }}</span>
                    </div>
                    
                    <div v-if="leave.approved_at" class="flex justify-between">
                      <span class="text-gray-500">Approved At:</span>
                      <span class="font-medium text-gray-900">{{ formatDateTime(leave.approved_at) }}</span>
                    </div>
                    
                    <div v-if="leave.rejection_reason" class="flex justify-between">
                      <span class="text-gray-500">Rejection Reason:</span>
                      <span class="font-medium text-gray-900">{{ leave.rejection_reason }}</span>
                    </div>
                  </div>
                </div>

                <!-- Reason -->
                <div class="bg-gray-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-900 mb-3">Reason</h4>
                  <p class="text-sm text-gray-900">{{ leave.reason }}</p>
                </div>

                <!-- Timestamps -->
                <div class="bg-gray-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-900 mb-3">Timestamps</h4>
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-500">Created:</span>
                      <span class="font-medium text-gray-900">{{ formatDateTime(leave.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-500">Last Updated:</span>
                      <span class="font-medium text-gray-900">{{ formatDateTime(leave.updated_at) }}</span>
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
import { EyeIcon } from '@heroicons/vue/24/outline';

// Props and emits
const props = defineProps({
  leave: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close']);

// Methods
const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatDateTime = (dateTime) => {
  return new Date(dateTime).toLocaleString();
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
</script> 