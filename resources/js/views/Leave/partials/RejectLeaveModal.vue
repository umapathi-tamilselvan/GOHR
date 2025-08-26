<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
              <XCircleIcon class="h-6 w-6 text-red-600" />
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Reject Leave Request
              </h3>
              <div class="mt-2">
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                  <h4 class="text-sm font-medium text-gray-900 mb-2">Leave Request Details</h4>
                  <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Employee:</strong> {{ leave.user.name }}</p>
                    <p><strong>Leave Type:</strong> {{ leave.leave_type.name }}</p>
                    <p><strong>Dates:</strong> {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}</p>
                    <p><strong>Total Days:</strong> {{ leave.total_days }} days</p>
                  </div>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                  <!-- Rejection Reason -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Rejection Reason *
                    </label>
                    <textarea
                      v-model="form.rejection_reason"
                      rows="4"
                      required
                      maxlength="1000"
                      placeholder="Please provide a reason for rejecting this leave request..."
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                    ></textarea>
                    <p class="mt-1 text-sm text-gray-500">
                      {{ form.rejection_reason.length }}/1000 characters
                    </p>
                  </div>

                  <!-- Warning -->
                  <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400" />
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                          Important Notice
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                          <p>Rejecting this leave request will notify the employee and they will need to submit a new request if they still need time off.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            type="submit"
            @click="submitForm"
            :disabled="loading || !isFormValid"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm"
          >
            <div v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            {{ loading ? 'Rejecting...' : 'Reject Leave Request' }}
          </button>
          <button
            type="button"
            @click="$emit('close')"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useToast } from 'vue-toastification';
import {
  XCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

// Props and emits
const props = defineProps({
  leave: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close', 'rejected']);

// Store
const leaveStore = useLeaveStore();
const toast = useToast();

// Reactive data
const form = ref({
  rejection_reason: '',
});

const loading = ref(false);

// Computed
const isFormValid = computed(() => {
  return form.value.rejection_reason.trim().length > 0;
});

// Methods
const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const submitForm = async () => {
  if (!isFormValid.value) {
    toast.error('Please provide a rejection reason');
    return;
  }

  loading.value = true;

  try {
    const result = await leaveStore.rejectLeave(props.leave.id, form.value.rejection_reason);
    
    if (result.success) {
      toast.success('Leave request rejected successfully');
      emit('rejected', result.data);
    } else {
      toast.error(result.error || 'Failed to reject leave request');
    }
  } catch (error) {
    toast.error('An unexpected error occurred');
    console.error('Error rejecting leave:', error);
  } finally {
    loading.value = false;
  }
};
</script> 