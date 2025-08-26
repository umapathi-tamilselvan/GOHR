<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
              <PencilIcon class="h-6 w-6 text-blue-600" />
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Edit Leave Request
              </h3>
              <div class="mt-2">
                <form @submit.prevent="submitForm" class="space-y-4">
                  <!-- Leave Type (Read-only) -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Leave Type
                    </label>
                    <input
                      :value="leave.leave_type.name"
                      type="text"
                      readonly
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 sm:text-sm"
                    />
                  </div>

                  <!-- Start Date -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Start Date *
                    </label>
                    <input
                      v-model="form.start_date"
                      type="date"
                      required
                      :min="today"
                      @change="calculateDays"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                  </div>

                  <!-- End Date -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      End Date *
                    </label>
                    <input
                      v-model="form.end_date"
                      type="date"
                      required
                      :min="form.start_date"
                      @change="calculateDays"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                  </div>

                  <!-- Total Days -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Total Days
                    </label>
                    <input
                      v-model="form.total_days"
                      type="number"
                      readonly
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 sm:text-sm"
                    />
                  </div>

                  <!-- Reason -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Reason *
                    </label>
                    <textarea
                      v-model="form.reason"
                      rows="3"
                      required
                      maxlength="1000"
                      placeholder="Please provide a reason for your leave request..."
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    ></textarea>
                    <p class="mt-1 text-sm text-gray-500">
                      {{ form.reason.length }}/1000 characters
                    </p>
                  </div>

                  <!-- Current Status -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Current Status
                    </label>
                    <span
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1"
                      :class="getStatusClasses(leave.status)"
                    >
                      {{ leave.status.charAt(0).toUpperCase() + leave.status.slice(1) }}
                    </span>
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
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm"
          >
            <div v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            {{ loading ? 'Updating...' : 'Update Leave Request' }}
          </button>
          <button
            type="button"
            @click="$emit('close')"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useLeaveStore } from '@/stores/leave';
import { useToast } from 'vue-toastification';
import { PencilIcon } from '@heroicons/vue/24/outline';

// Props and emits
const props = defineProps({
  leave: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['close', 'updated']);

// Store
const leaveStore = useLeaveStore();
const toast = useToast();

// Reactive data
const form = ref({
  start_date: '',
  end_date: '',
  total_days: 0,
  reason: '',
});

const loading = ref(false);

// Computed
const today = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const isFormValid = computed(() => {
  return form.value.start_date &&
         form.value.end_date &&
         form.value.reason.trim() &&
         form.value.total_days > 0;
});

// Methods
const calculateDays = () => {
  if (form.value.start_date && form.value.end_date) {
    const start = new Date(form.value.start_date);
    const end = new Date(form.value.end_date);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    form.value.total_days = diffDays + 1; // Include both start and end dates
  }
};

const submitForm = async () => {
  if (!isFormValid.value) {
    toast.error('Please fill in all required fields correctly');
    return;
  }

  loading.value = true;

  try {
    const result = await leaveStore.updateLeave(props.leave.id, form.value);
    
    if (result.success) {
      toast.success('Leave request updated successfully');
      emit('updated', result.data);
    } else {
      toast.error(result.error || 'Failed to update leave request');
    }
  } catch (error) {
    toast.error('An unexpected error occurred');
    console.error('Error updating leave:', error);
  } finally {
    loading.value = false;
  }
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

const initializeForm = () => {
  form.value = {
    start_date: props.leave.start_date,
    end_date: props.leave.end_date,
    total_days: props.leave.total_days,
    reason: props.leave.reason,
  };
};

// Lifecycle
onMounted(() => {
  initializeForm();
});
</script> 